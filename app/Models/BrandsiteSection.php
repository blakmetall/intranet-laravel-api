<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class BrandsiteSection extends Model
{

    use Sluggable;

    protected $table = 'brandsite_sections';
    public $timestamps = false;
    protected $guarded = [
        'slug',
        'is_predefined',

        'features', // do not attempt to save features on fill
    ];
    protected $casts = [
        'is_predefined' => 'integer'
    ];

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'name_en'
            ]
        ];
    }

    // relation: brandsite section has many features to select
    public function features(){
        return $this->hasMany('App\Models\BrandsiteSectionFeature');
    }

    // relation: hotels with current brandsite section related
    public function hotels(){
        return $this->belongsToMany('App\Models\Hotel', 'hotel_brandsite_sections')
            ->as('brandsite_section') // pivot table named as files
            ->withPivot('is_enabled');
    }

    // relation: hotel brandsite section linked
    public function hotelBrandsiteSections(){
        return $this->hasMany('App\Models\HotelBrandsiteSection');
    }



    public function _data(){
        $data = $this;
        $data->features = $this->features()->orderBy('name_es', 'asc')->get();
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];
        if($this->is_predefined){
            $denials[] = 'Esta sección es predeterminada y no se puede eliminar';
        }

        return $denials;
    }

    public function _afterDelete(){
        if($this->features){
            foreach($this->features as $feature){
                $feature->delete();
                $feature->_afterDelete();
            }
        }

        if($this->hotelBrandsiteSections){
            foreach($this->hotelBrandsiteSections as $hotel_section){
                $hotel_section->delete();
                $hotel_section->_afterDelete();
            }
        }
    }

    public function _saveFeatures($features){

        $featuresToSave = [];

        $features_ids = [];

        if(count($features)){
            foreach($features as $feature){
                $tmpFeature = BrandsiteSectionFeature::updateOrCreate( ['id' => $feature['id'] ], $feature );

                $features_ids[] = $tmpFeature->id;

                $featuresToSave[] = $tmpFeature;
            }
        }

        $this->features()->saveMany( $featuresToSave );

        $this->_deleteFeaturesByReverseIDs($features_ids);

    }

    public function _deleteFeaturesByReverseIDs($features_ids){
        $query = BrandsiteSectionFeature::where('brandsite_section_id', $this->id);
        $query->whereNotIn('id', $features_ids);
        $query->delete();
    }

}
