<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;

class MahgazineArticle extends Model
{

    use Sluggable, SoftDeletes;

    protected $table = 'mahgazine_section_articles';
    public $timestamps = true;
    protected $dates = ['deleted_at'];
    protected $guarded = [
        'article_file_id',
        'slug',
    ];
    protected $casts = [
        'mahgazine_edition_section_id' => 'integer',
        'hotel_id' => 'integer',
        'article_file_id' => 'integer',
        'order' => 'integer',
    ];
    public $folder_name = 'mahgazine_articles';

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    // polymorphic relation: files
    public function files(){
        return $this->morphMany('App\Models\File', 'polymorphic');
    }

    // relation: article file
    public function articleFile(){
        return $this->morphMany('App\Models\File', 'polymorphic')
            ->where('input_id', 'article_file');
    }

    // relation: section owner
    public function section(){
        return $this->belongsTo('App\Models\MahgazineSection', 'mahgazine_edition_section_id');
    }

    public function hotel(){
        return $this->belongsTo('App\Models\Hotel');
    }



    // non relational methods

    public function _data(){
        $data = $this;
        $data->section = $this->section;

        // cover file
        $data->article_file = $this->articleFile()->first();
        if($data->article_file && $data->article_file->is_image){
            $data->article_file->media = MediaSize::_get($data->article_file);
        }

        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){
        if($this->files()->count()){
            foreach($this->files as $file){
                $file->_removeFiles();
                $file->forceDelete();
                $file->_afterDelete();
            }
        }
    }

}
