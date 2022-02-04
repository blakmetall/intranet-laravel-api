<?php

namespace App\Http\Controllers;

use App\Models\File;
use Storage;

class DownloadsCtrl extends Controller
{

    public function download($file_id, $file_slug, $media_size = ''){
        $file = File::where('id', $file_id)->where('slug', $file_slug)->first();
        $filepath_raw = $file->filepath;
        $image_dimension_format = '';

        if(!$media_size == ''){
            if($file->sizes()->count()){
                foreach($file->sizes as $size){
                    if($size->slug == $media_size){
                        $filepath_raw = $size->pivot->filepath;
                        $image_dimension_format = '-' . $media_size;
                    }
                }
            }
        }

        $filepath = Storage::path($filepath_raw);

        if($file && file_exists($filepath)){
            $download_filename = $file->slug . $image_dimension_format . '.' . $file->extension;
            return response()->download($filepath, $download_filename );
        }else{
            return view('failed-download');

        }
    }
}
