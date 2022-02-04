<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class BrandsiteSectionFeature extends Model
{

    use Sluggable;

    protected $table = 'brandsite_section_features';
    public $timestamps = false;
    protected $guarded = [
        'slug',
    ];
    protected $casts = [
        'brandsite_section_id' => 'integer',
    ];

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'name_en'
            ]
        ];
    }


    // relation: brandsite section
    public function brandsiteSection(){
        return $this->belongsTo('App\Models\BrandsiteSection');
    }

    // relation: youtube videos with the brandsite section category assigned
    public function youtubeVideos(){
        return $this->belongsToMany('App\Models\YoutubeVideo');
    }

    // relation: records from "files" that has current feature
    public function files(){
        return $this->belongsToMany('App\Models\File', 'file_has_features', 'brandsite_section_feature_id', 'file_id');
    }


    public function _data(){
        $data = $this;
        //$data->brandsiteSection = $this->brandsiteSection;
        return $this;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){
        $this->youtubeVideos()->detach();
        $this->files()->detach();
    }

}
