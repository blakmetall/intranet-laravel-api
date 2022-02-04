<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MahgazineSection extends Model
{

    use SoftDeletes;
    
    protected $table = 'mahgazine_edition_sections';
    public $timestamps = true;
    protected $dates = ['deleted_at'];
    protected $guarded = [
        'id',
    ];
    protected $casts = [
        'mahgazine_edition_id' => 'integer',
        'order' => 'integer',
    ];
    public $folder_name = 'mahgazine_sections';

    // relation: edition owner of current section edition
    public function edition(){
        return $this->belongsTo('App\Models\MahgazineEdition', 'mahgazine_edition_id');
    }

    // relation: articles of current section edition
    public function articles(){
        return $this->hasMany('App\Models\MahgazineArticle', 'mahgazine_edition_section_id');
    }


    // non relational methods

    public function _data(){
        $data = $this;
        $data->edition = $this->edition;
        $data->articles = ($this->articles()->count()) ? $this->articles : [];
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){
        if($this->articles()->count()){
            foreach($this->articles as $article){
                $article->forceDelete();
                $article->_afterDelete();
            }
        }
    }

}
