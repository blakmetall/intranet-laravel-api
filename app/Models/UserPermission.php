<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class UserPermission extends Model
{

    use Sluggable;

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'user_permissions_group_id' => 'integer',
    ];

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    // relation: users with current permission enabled
    public function users(){
        return $this->belongsToMany('App\Models\User', 'user_has_permissions');
    }

    // relation: group that owns this permission
    public function group(){
        return $this->belongsTo('App\Models\UserPermissionsGroup');
    }

    // non relational methods

    public function _data(){
        $data = $this;

        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];

        // only admin can delete

        return $denials;
    }

    public function _afterDelete(){}

}
