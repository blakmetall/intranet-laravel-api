<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Folder extends Model
{
    use SoftDeletes, Sluggable;

    protected $table = 'folders';
    public $timestamps = true;
    protected $dates = ['deleted_at'];
    protected $guarded = [
        'polymorphic_id',
        'polymorphic_type',
        'is_root',
        'user_owner_id',

        'settings'
    ];
    protected $casts = [
        'polymorphic_id' => 'integer',
        'is_root' => 'integer',
        'is_private' => 'integer',
        'is_featured' => 'integer',
        'user_owner_id' => 'integer',
    ];
    public $folder_name = 'folders';

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    // polymorphic relation: allows any other table to have folders (folders can have files)
    // used by: BrandsiteSectionFileManager
    public function polymorphic(){
        return $this->morphTo();
    }

    // polymorphic relation: folder has many files
    public function files(){
        return $this->morphMany('App\Models\File', 'polymorphic');
    }

    // polymorphic relation: folder can have many folders
    public function folders(){
        return $this->morphMany('App\Models\Folder', 'polymorphic');
    }

    // relation: folder can have multiple youtube videos assigned
    public function youtubeVideos(){
        return $this->hasMany('App\Models\YoutubeVideo');
    }

    // relation: user shared on folder
    public function permissions(){
        return $this->belongsToMany('App\Models\User', 'folder_permissions');
    }

    // relation: owner of the folder
    public function owner(){
        return $this->belongsTo('App\Models\User', 'user_owner_id');
    }

    public function _data(){
        $data = $this;
        //$data->permissions = $this->permissions;
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];

        if( $this->is_private && $this->user_owner_id != Auth::id()){

            if( $this->owner->isCorporative() || $this->owner->isRegular() ){
                $denials[] = __('messages.admin-folder-delete-restriction');
            }

        }

        return $denials;
    }

    public function _afterDelete(){

        // remove child folders
        if($this->folders()->count()){
            foreach($this->folders as $folder){
                $folder->forceDelete();
                $folder->_afterDelete();
            }
        }

        // remove permissions visibility
        if($this->permissions()->count()){
            $this->permissions()->detach();
        }

        // remove files attached
        if($this->files()->count()){
            foreach($this->files as $file){
                $file->_removeFiles();
                $file->forceDelete();
                $file->_afterDelete();
            }
        }

        /** // remove youtube videos
        if($this->youtubeVideos()->count()){
            foreach($this->youtubeVideos as $video){
                $video->delete();
                $video->_afterDelete();
            }
            $this->youtubeVideos()->detach();
        }*/
    }

    public function _updateFolderPermissions($permissions){
        $users_permitted_ids = [];

        if(is_array($permissions) && count($permissions)){
            foreach($permissions as $permission){
                if($permission['is_permitted']){
                    $users_permitted_ids[ $permission['id'] ] = []; // user_id to be allowed (sent on request)
                }
            }
        }

        $this->permissions()->sync($users_permitted_ids);
    }

    // Returns the model to be used based on polymorphic_type string
    public static function _getPolymorphicModel($polymorphic_type){
        switch($polymorphic_type){
            case 'Folder':
                return new Folder;
                break;
            case 'FolderSection':
                return new FolderSection;
                break;
            case 'HotelBrandsiteSection':
                return new HotelBrandsiteSection;
                break;
        }

        return false;
    }

    // Returns the folder tree structure including childs ( recursive function )
    public static function _setTree($folder){
        $tree = [];
        $tree['folder'] = $folder;
        $tree['folder']->children = [];

        $childFolders = $folder->folders()->orderBy('name', 'asc')->get();
        if($childFolders){

            $children = [];

            foreach($childFolders as $k => $childFolder){
                $children[] = Folder::_setTree($childFolder);
            }

            $tree['folder']->children = $children;

        }

        return $tree;
    }
}
