<?php

namespace App\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Folder;
use Auth;

class FoldersCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function get(Folder $folder) {
        return $folder;
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(Folder $folder, Request $request){
        return $this->saveData($folder, $request)->_data();
    }

    public function delete($id, Request $request){
        $folder = Folder::withTrashed()->find($id);
        if($folder){
            if($request->forceDelete){
                $denials = $folder->_deleteAllowed();
                if(!count($denials)) {
                    $folder->forceDelete();
                    $folder->_afterDelete();
                }else{
                    throw new HttpResponseException(
                        response()->json(['errors' => $denials], 400)
                    );
                }
            }else{
                $folder->delete();
            }
            return $folder->_data();
        }
        return array();
    }

    public function restore($id){
        $folder = Folder::withTrashed()->where('id', $id)->first();
        if($folder){
            $folder->restore();
            return $folder->_data();
        }
        return array();
    }

    // Returns direct folder childs from a specific folder
    public function getChilds(Folder $folder, Request $request) {
        $query = $folder->folders();

        if($request->is_private && $request->is_private == 'true'){
            $query->where('is_private', 1);
        }

        if($request->search){
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        return $query
            ->with('permissions')
            ->with('owner.profile')
            ->orderBy('name', 'asc')
            ->orderBy('is_featured', 'desc')
            ->get();
    }

    // Returns the root folder or creates new root folder
    public function getRoot($polymorphic_id, $polymorphic_type) {
        $rootFolder = [];

        $model = Folder::_getPolymorphicModel($polymorphic_type);
        if($model){
            $obj = $model->find($polymorphic_id);
            if($obj){
                $rootFolder = $obj->rootFolder();

                if(!$rootFolder){ // create root folder
                    $folder = new Folder;
                    $folder->name = 'Root';
                    $folder->is_root = 1;
                    $folder->save();

                    $obj->folders()->save($folder);

                    $rootFolder = $obj->rootFolder();
                }
            }
        }

        return $rootFolder;
    }

    // Returns json with folder structure tree; from parent to childs
    public function getRootTree(Folder $folder) {
        $found = true;
        while(!$folder->is_root){
            $folder = Folder::find($folder->polymorphic_id);
            if(!$folder){
                $found = false;
                break;
            }
        }

        return ($found) ? Folder::_setTree($folder) : [];
    }

    // Returns breadcrumbs list for the folder
    public function getBreadcrumbs(Folder $folder) {
        $breadcrumbs = [$folder];

        while(!$folder->is_root){
            $folder = Folder::find($folder->polymorphic_id);
            if(!$folder){
                break;
            }

            $breadcrumbs[] = $folder;
        }

        return array_reverse($breadcrumbs);
    }

    // Returns users permitted for a folder
    public function getUsersPermitted(Folder $folder){
        return $folder->permissions;
    }

    // returns root folder features to be selected on file upload
    public function getRootFolderAvailableFeatures(Request $request){
        $features = [];
        if($request->polymorphic_id && $request->polymorphic_type){
            $model = Folder::_getPolymorphicModel($request->polymorphic_type);

            if($model !== false && class_basename($model) == 'HotelBrandsiteSection'){
                $hotelBrandsiteSection = $model->where('id', $request->polymorphic_id)->first();
                if($hotelBrandsiteSection && $hotelBrandsiteSection->brandsiteSection()->count()){
                    $features = $hotelBrandsiteSection->brandsiteSection->features;
                }
            }
        }

        return $features;
    }

    private function saveData($folder, $request){
        if(!$folder){
            $folder = new Folder;
            $folder->user_owner_id = Auth::id();
        }
        $this->validateData($folder, $request);

        $folder->fill( $request->all());
        $folder->save();

        // update users permissions
        $folder->_updateFolderPermissions($request->settings['folderPermissions']);

        // save folder to polymorphic model (according to request polymorphic type)
        $model = Folder::_getPolymorphicModel($request->polymorphic_type);
        if($model){
            $obj = $model->find($request->polymorphic_id);
            if($obj){
                $obj->folders()->save($folder);
            }
        }


        return $folder;
    }

    private function validateData($folder, $request){
        $rules = [
            'name' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }
    }


}
