<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class QualityAssuranceVisitStatus extends Model
{

    use Sluggable;

    protected $table = 'quality_assurance_visit_status';
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

    public function assuranceVisits(){
        return $this->hasMany('App\Models\QualityAssuranceVisit');
    }

    // non relational methods

    public function _data(){
        $data = $this;
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];

        if("is not admin"){
            $denials[] = "Para eliminar esta categoría por favor contacte al administrador.";
        }

        return $denials;
    }

    public function _afterDelete(){}

}
