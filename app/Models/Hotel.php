<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;

class Hotel extends Model
{
    use SoftDeletes;
    use Sluggable;

    protected $table = 'hotels';
    public $timestamps = true;
    protected $dates = ['deleted_at'];
    protected $guarded = [
        'hotel_address_id',
        'presentation_file_id',
        'logo_file_id',
        'order',

        'address', // address polymorphic, avoid to save via "fill" method
    ];
    protected $casts = [
        'hotel_address_id' => 'integer',
        'presentation_file_id' => 'integer',
        'logo_file_id' => 'integer',
        'stars' => 'integer',
        'is_enabled' => 'integer',
        'order' => 'integer',
    ];
    public $folder_name = 'hotels';

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }


    // polymorphic relation: addresses of hotel
    public function addresses(){
        return $this->morphMany('App\Models\Address', 'polymorphic');
    }

    public function address(){
        return $this->addresses()->first();
    }

    public function hasAddress(){
        return ($this->addresses()->count()) ? true : false;
    }

    // polymorphic relation: files of user
    public function files(){
        return $this->morphMany('App\Models\File', 'polymorphic');
    }

    // relation: photo
    public function logo(){
        return $this->morphMany('App\Models\File', 'polymorphic')
            ->where('input_id', 'logo_file');
    }

    // relation: presentation image
    public function presentationImage(){
        return $this->morphMany('App\Models\File', 'polymorphic')
            ->where('input_id', 'presentation_file');
    }

    // relation: hotel profiles
    public function profiles(){
        return $this->hasMany('App\Models\UserProfile');
    }

    // relation: brandsite sections (linked via belongsToMany)
    public function brandsiteSections(){
        return $this->belongsToMany('App\Models\BrandsiteSection', 'hotel_brandsite_sections')
            ->as('brandsite_section') // pivot table named as files
            ->withPivot('is_enabled', 'id');
    }

    // relation: hotel brandsite sections (linked via hasMany)
    public function hotelBrandsiteSections(){
        return $this->hasMany('App\Models\HotelBrandsiteSection');
    }


    // relation: articles on a specific mahgazine section
    public function mahgazineArticles(){
        return $this->hasMany('App\Models\MahgazineArticle');
    }

    // relation: quality assurance visits
    public function qualityAssuranceVisits(){
        return $this->hasMany('App\Models\QualityAssuranceVisit');
    }

    // relation: events created for this hotel
    public function events(){
        return $this->hasMany('App\Models\Event');
    }

    // non relational methods

    public function _data(){
        $data = $this;
        $data->address = ($this->hasAddress()) ? $this->address() : null;

        // logo
        $data->logo = $this->logo()->first();
        if($data->logo && $data->logo->is_image){
            $data->logo->media = MediaSize::_get($data->logo);
        }

        // presentation file img
        $data->presentation_file = $data->presentationImage()->first();
        if($data->presentation_file && $data->presentation_file->is_image){
            $data->presentation_file->media = MediaSize::_get($data->presentation_file);
        }

        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){

        // quality assurance visits
        if( $this->qualityAssuranceVisits()->count() ){
            foreach($this->qualityAssuranceVisits as $assurance_visit){
                $assurance_visit->delete();
                $assurance_visit->_afterDelete();
            }
        }

        // quality assurance visits
        if( $this->events()->count() ){
            foreach($this->events as $event){
                $event->delete();
                $event->_afterDelete();
            }
        }

        // delete address
        if( $this->hasAddress() ){
            foreach($this->addresses as $address){
                $address->forceDelete();
                $address->_afterDelete();
            }
        }

        // delete brandsite sections (with files)
        if($this->hotelBrandsiteSections()->count()){
            foreach($this->hotelBrandsiteSections as $section){
                $section->delete();
                $section->_afterDelete();
            }
        }

        // remove files directly attached to this record
        if($this->files()->count()){
            foreach($this->files as $file){
                $file->_removeFiles();
                $file->forceDelete();
                $file->_afterDelete();
            }
        }
    }

    public function _initializeBrandsiteSections(){

        // only created once per hotel brandsite section
        if($this->brandsiteSections()->count() == 0){
            $sections = BrandsiteSection::orderBy('name_en', 'asc')->get();

            if(count($sections)){
                foreach($sections as $section){
                    $hotelBrandsiteSection = new HotelBrandsiteSection;
                    $hotelBrandsiteSection->hotel_id = $this->id;
                    $hotelBrandsiteSection->brandsite_section_id = $section->id;
                    $hotelBrandsiteSection->is_enabled = 0;
                    $hotelBrandsiteSection->save();
                }
            }
        }

    }

}
