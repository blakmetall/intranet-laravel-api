<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class UserPermissionsGroup extends Model
{
    use Sluggable;

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'is_administrative_group' => 'integer',
    ];

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    // relation: individual permissions from current permissions group
    public function permissions(){
        return $this->hasMany('App\Models\UserPermission');
    }


    // non relational methods

    public function _data(){
        $data = $this;
        $data->permissions = $this->permissions;
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];

        // only admin can delete

        return $denials;
    }

    public function _afterDelete(){}

}
