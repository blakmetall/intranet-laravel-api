<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Storage;

class MediaSize extends Model
{

    use Sluggable;

    protected $table = 'media_sizes';
    public $timestamps = false;
    //protected $dates = ['deleted_at'];
    protected $guarded = [
        'slug',
        'width',
        'height',
        'filepath',
        'url'
    ];
    protected $casts = [
        'width' => 'double',
        'height' => 'double',
    ];

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    // relation: image size is related to files (image files only)
    public function files(){
        return $this->belongsToMany('App\Models\File', 'files_has_media_sizes')
            ->withPivot(['filepath', 'url']);
    }

    public static function _get($file){
        if( $file && $file->sizes()->count() ){
            $media = [];
            foreach($file->sizes as $size){
                $size->url = $size->pivot->url;
                $media[ $size->slug ] = $size;
            }
            return $media;
        }

        return false;
    }

    public static function _generate($file){
        MediaSize::_removeMedia($file);

        if($file->filepath != '' && $file->is_image && MediaSize::_isAllowedMimeType($file->mime_type)) {

            // default media sizes
            $sizes = MediaSize::all();

            if(!is_array($sizes)){
                foreach ($sizes as $size) {
                    $_resized = false;

                    $img = \Image::make(Storage::path($file->filepath)); // INTERVENTION OBJECT

                    if($size->fit){
                        // RESIZE AND FIT
                        $img->fit($size->width, $size->height, function ($constraint) {
                            $constraint->upsize();
                        });
                        $_resized = true;
                    }else{
                        // RESIZE AND CROP ONLY IF COMPLY WITH DIMENSIONS
                        if($file->width >= $size->width && $file->height >= $size->height){
                            $ratio = min($file->width / $size->width, $file->height / $size->height);
                            $new_width = $file->width / $ratio;
                            $new_height = $file->height / $ratio;

                            $img->resize($new_width, $new_height);
                            $_resized = true;
                        }
                    }

                    if($_resized){
                        // save to storage
                        $filename = $file->slug . '-' . $size->slug . '.' . $file->extension;
                        $filepath = $file->system_folder . '/' . $file->polymorphic_id . '/' . $filename;
                        $img->save(Storage::path($filepath));

                        // attach media size to $file record
                        $url = env('APP_URL') . Storage::url($filepath);
                        $file->sizes()->attach($size->id, ['filepath' => $filepath, 'url' => $url]);
                    }

                }
            }

        }
    }

    // allowed mime type for thumbnail generation
    public static function _isAllowedMimeType($mimeType){
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/x-bmp'
        ];

        return in_array($mimeType, $allowedMimeTypes) ? true : false;
    }

    public static function _removeMedia($file){
        if($file->sizes()->count()){
            $detach_ids = [];
            foreach($file->sizes as $imgSize){
                if(file_exists(Storage::path($imgSize->pivot->filepath))){
                    @unlink(Storage::path($imgSize->pivot->filepath));
                }

                $detach_ids[] = $imgSize->id;
            }
            $file->sizes()->detach( $detach_ids );
        }
    }

}
