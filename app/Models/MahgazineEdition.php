<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MahgazineEdition extends Model
{
    use SoftDeletes;

    protected $table = 'mahgazine_editions';
    public $timestamps = true;
    protected $dates = ['deleted_at'];
    protected $guarded = [
        'id',
        'cover_file_id',

        'start_datetime',
        'start_date',
        'start_time',

        'end_datetime',
        'end_date',
        'end_time'
    ];
    protected $casts = [
        'cover_file_id' => 'integer',
        'is_published' => 'integer',
    ];
    public $folder_name = 'mahgazine_editions';

    // polymorphic relation: files
    public function files(){
        return $this->morphMany('App\Models\File', 'polymorphic');
    }

    // relation: document file
    public function coverFile(){
        return $this->morphMany('App\Models\File', 'polymorphic')
            ->where('input_id', 'cover_file');
    }

    // relation: sections linked to this edition (might be owned or not) ** full data relation
    public function sections(){
        return $this->hasMany('App\Models\MahgazineSection', 'mahgazine_edition_id');
    }

    public function _data(){
        $data = $this;
        $data->sections = ($this->sections()->count()) ? $this->sections : [];

        // cover file
        $data->cover_file = $this->coverFile()->first();
        if($data->cover_file && $data->cover_file->is_image){
            $data->cover_file->media = MediaSize::_get($data->cover_file);
        }

        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){

        if($this->sections()->count()){
            foreach($this->sections as $section){
                $section->forceDelete();
                $section->_afterDelete();
            }
        }

        if($this->files()->count()){
            foreach($this->files as $file){
                $file->_removeFiles();
                $file->forceDelete();
                $file->_afterDelete();
            }
        }
    }

}
