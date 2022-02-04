<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelBrandsiteSection extends Model
{

    protected $table = 'hotel_brandsite_sections';
    public $timestamps = false;
    protected $guarded = [
        'hotel_id',
        'brandsite_section_id',
    ];
    protected $casts = [
        'hotel_id' => 'integer',
        'brandsite_section_id' => 'integer',
        'is_enabled' => 'integer',
    ];

    // polymorphic relation: file manager section has root folder
    public function rootFolder(){
        return $this->morphMany('App\Models\Folder', 'polymorphic')
            ->where('is_root', 1)
            ->first();
    }

    public function folders(){
        return $this->morphMany('App\Models\Folder', 'polymorphic');
    }

    // relation: hotel owner of brandsite section file manager
    public function hotel(){
        return $this->belongsTo('App\Models\Hotel');
    }

    // relation: brandsite section data
    public function brandsiteSection(){
        return $this->belongsTo('App\Models\BrandsiteSection');
    }


    public function _data(){
        $data = $this;
        $data->hotel = $this->hotel;
        $data->brandsiteSection = $this->brandsiteSection;
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];
        $denials[] = "Las secciones de archivos solo se pueden deshabilitar o habilitar. Para eliminar completamente, contactar al administrador.";
        return $denials;
    }

    public function _afterDelete(){
        $rootFolder = $this->rootFolder();
        if($rootFolder){
            $rootFolder->forceDelete();
            $rootFolder->_afterDelete();
        }
    }

}
