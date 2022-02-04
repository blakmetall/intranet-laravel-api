<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\MediaSize;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\File;
use Auth;
use Storage;

class FilesCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    /**
     * Uploads single file to files table and returns file id to be saved in different tables.
     */
    public function singleUpload($item_type, $item_id, $input_id, Request $request){
        $res = ['success' => false];

        $polymorphic_model = File::_getPolymorphicModel($item_type);
        $file_parent_model = $polymorphic_model->find($item_id);

        if($file_parent_model && $request->hasFile('file')){
            // search for old file to be replaced
            $original_file = $file_parent_model->files()->where('input_id', $input_id)->first();

            // initialize replacement flag
            $replace_file = ($original_file) ? true : false;

            // removes old media
            if($replace_file){
                $file = clone $original_file;
                MediaSize::_removeMedia($original_file);
            }else{
                $file = new File;
            }

            $requestFile = $request->file('file');

            $file->input_id = $input_id;
            $file->is_image = $this->_uploadIsImage( $request );

            // get image dimensions if applies
            if($file->is_image){
                $_image_size = getimagesize($requestFile);
                $file->width = $_image_size[0];
                $file->height = $_image_size[1];
            }else{
                $file->width = '';
                $file->height = '';
            }

            $file->name = File::_removeExtension($requestFile->getClientOriginalName());
            $file->original_name = $requestFile->getClientOriginalName();
            $file->extension = $requestFile->getClientOriginalExtension();
            $file->mime_type = $requestFile->getClientMimeType();
            $file->size_bytes = $requestFile->getSize();
            $file->version = $file->version++;
            $file->slug = null; // force regeneration
            $file->save();

            // file path data
            $filename = $file->slug . '.' . $file->extension;
            $childpath = $file_parent_model->id . '/' . $filename;

            // folder name inside storage folder (public/storage)
            $file->system_folder = $file_parent_model->folder_name;

            // file path and url
            $file->filepath = $file->system_folder . '/' . $childpath;
            $file->url = env('APP_URL') . Storage::url($file->filepath);
            $file->save();

            // move file to folders folder inside "storage" folder
            if($requestFile->storeAs($file->system_folder, $childpath)){

                // removes original file if replaced by another
                if($replace_file && file_exists(Storage::path($original_file->filepath))){
                    if($file->slug != $original_file->slug){
                        @unlink(Storage::path($original_file->filepath));
                    }
                }

                // attach to folder
                $file_parent_model->files()->save($file);

                // assign input_id from file->id generated (to allow quick access to the parent_model)
                // respect naming convention string + '_id'
                $input_id_string = $input_id . '_id';
                $file_parent_model->{$input_id_string} = $file->id;
                $file_parent_model->save();

                // make media sizes
                MediaSize::_generate($file);

                $res['file'] = $file;
                $res['file']->media = MediaSize::_get($file);
                $res['success'] = true;
            }else{
                // if upload fails, we delete new file or a new record was created
                // or return the original file and reset all data
                if($replace_file){
                    $original_file->save();
                    MediaSize::_generate($original_file);
                }else{
                    $file->forceDelete(); // if file move fails, delete old data
                }
            }
        }

        return $res;
    }

    public function update(File $file, Request $request){
        $res = ['success' => false];

        // update file
        if($request->hasFile('file') && $file->folder){
            $original_file = clone $file;
            MediaSize::_removeMedia($original_file);

            $requestFile = $request->file('file');

            // get image dimensions if applies
            $file->is_image = $this->_uploadIsImage( $request );
            if($file->is_image){
                $_image_size = getimagesize($requestFile);
                $file->width = $_image_size[0];
                $file->height = $_image_size[1];
            }else{
                $file->width = '';
                $file->height = '';
            }

            $file->name = File::_removeExtension($requestFile->getClientOriginalName());
            $file->original_name = $requestFile->getClientOriginalName();
            $file->extension = $requestFile->getClientOriginalExtension();
            $file->mime_type = $requestFile->getClientMimeType();
            $file->size_bytes = $requestFile->getSize();
            $file->version = ((int) $file->version) + 1; // first creation
            $file->slug = null; // force regeneration
            $file->save();

            // file path data
            $filename = $file->slug . '.' . $file->extension;
            $childpath = $file->folder->id . '/' . $filename;

            // folder name inside storage folder (public/storage)
            $file->system_folder = 'folders';

            // file path and url
            $file->filepath = $file->system_folder . '/' . $childpath;
            $file->url = env('APP_URL') . Storage::url($file->filepath);
            $file->save();

            // move file to folders folder inside "storage" folder
            if($requestFile->storeAs($file->system_folder, $childpath)){

                // removes original file
                if(file_exists(Storage::path($original_file->filepath))){
                    if($file->slug != $original_file->slug){
                        @unlink(Storage::path($original_file->filepath));
                    }
                }

                // make media sizes
                MediaSize::_generate($file);

                $res['success'] = true;
            }else{
                $original_file->save();
                MediaSize::_generate($original_file);
            }
        }

        // featured status and flip page flag
        $file->is_featured = $request->is_featured || '';
        $file->flip_page_enabled = ($file->extension == 'pdf' && $request->flip_page_enabled) ? 1 : '';
        $file->save();

        // update file features ( basically categories )
        if($request->features){
            $res['success'] = true;
            $file->_updateFeatures($request->features);
        }


        if($res['success']){
            $res['file'] = $file;
            $res['file']->media = MediaSize::_get($file);
        }

        return $res;
    }

    public function delete($id, Request $request){
        $file = File::withTrashed()->find($id);
        if($file){
            if($request->forceDelete){
                $file->_removeFiles();
                $file->forceDelete();
                $file->_afterDelete();
            }else{
                $file->delete();
            }
        }
        return array();
    }

    public function getFromFolder(Folder $folder, Request $request){
        $query = $folder->files();

        // filter by name
        if($request->search){
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // filter by features(categories)
        if($request->features && is_array($request->features)){
            $ids = [];
            foreach($request->features as $feature){
                if($feature['is_enabled']){
                    $ids[] = $feature['id'];
                }
            }

            if(count($ids)){
                $query->whereHas('features', function($q) use ($ids){
                     $q->whereIn('brandsite_section_features.id', $ids);
                });
            }
        }

        $files = $query->with(['sizes', 'features'])
            ->orderBy('files.name', 'asc')
            ->orderBy('files.is_featured', 'desc')
            ->get();

        // prepare media image sizes
        foreach($files as $k => $file){
            $files[$k]->media = MediaSize::_get($file);
        }

        return $files;
    }

    public function uploadToFolder(Folder $folder, Request $request){
        $res = [ 'success' => false ];

        if($request->hasFile('file')){
            $requestFile = $request->file('file');

            $file = new File;
            $file->is_image = $this->_uploadIsImage( $request );

            // get image dimensions if applies
            if($file->is_image){
                $_image_size = getimagesize($requestFile);
                $file->width = $_image_size[0];
                $file->height = $_image_size[1];
            }

            $file->name = File::_removeExtension($requestFile->getClientOriginalName());
            $file->original_name = $requestFile->getClientOriginalName();
            $file->extension = $requestFile->getClientOriginalExtension();
            $file->mime_type = $requestFile->getClientMimeType();
            $file->size_bytes = $requestFile->getSize();
            $file->version = 1; // first creation
            $file->save();

            // file path data
            $filename = $file->slug . '.' . $file->extension;
            $childpath = $folder->id . '/' . $filename;

            // folder name inside storage folder (public/storage)
            $file->system_folder = 'folders';

            // file path and url
            $file->filepath = $file->system_folder . '/' . $childpath;
            $file->url = env('APP_URL') . Storage::url($file->filepath);
            $file->save();

            // move file to folders folder inside "storage" folder
            if($requestFile->storeAs($file->system_folder, $childpath)){

                // attach to folder
                $folder->files()->save($file);

                // make media sizes
                MediaSize::_generate($file);

                $res['file'] = $file;
                $res['file']->media = MediaSize::_get($file);
                $res['success'] = true;
            }else{
                $file->forceDelete(); // if file move fails, delete old file record
            }
        }

        return $res;
    }

    public function updateFeatures(File $file, Request $request){
        $file->_updateFeatures($request->features);
    }

    public function updateFeaturedStatus(File $file, Request $request){
        $file->is_featured = $request->is_featured || '';
        $file->save();
    }

    public function updateFlipPageStatus(File $file, Request $request){
        $file->flip_page_enabled = ($file->extension == 'pdf' && $request->flip_page_enabled) ? 1 : '';
        $file->save();
    }

    private function _uploadIsImage($request){
        $rules = ['file' => 'mimes:jpeg,jpg,png,gif,tif,tiff,bmp,ico,webp,svg,html'];
        $validator = Validator::make( $request->all(), $rules );

        return ($validator->fails()) ? 0: 1;
    }
}
