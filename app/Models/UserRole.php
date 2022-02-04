<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class UserRole extends Model
{
    use Sluggable;

    protected $table = 'user_roles';
    public $timestamps = false;
    protected $guarded = [
        'slug',
    ];

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    // relation: user with current rol
    public function users(){
        return $this->hasMany('App\Models\User', 'user_role_id');
    }

}
