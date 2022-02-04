<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FolderSection extends Model
{
    use SoftDeletes, Sluggable;

    protected $table = 'folders_sections';
    public $timestamps = true;
    public $folder_name = 'folders_sections';

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    // polymorphic relation: file manager section has root folder
    public function rootFolder(){
        return $this->morphMany('App\Models\Folder', 'polymorphic')
            ->where('is_root', 1)
            ->first();
    }

    public function folders(){
        return $this->morphMany('App\Models\Folder', 'polymorphic');
    }

    public function _data(){
        $data = $this;
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){
        $rootFolder = $this->rootFolder();
        if($rootFolder){
            $rootFolder->forceDelete();
            $rootFolder->_afterDelete();
        }
    }
}
