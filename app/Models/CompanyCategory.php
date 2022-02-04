<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class CompanyCategory extends Model
{
    use Sluggable;

    protected $table = 'company_categories';
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

    // relation: companies from current category
    public function companies(){
        return $this->hasMany('App\Models\Company');
    }


    public function _data(){
        return $this;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){}

}
