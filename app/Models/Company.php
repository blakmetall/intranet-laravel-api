<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;


    protected $table = 'companies';
    public $timestamps = true;
    protected $dates = ['deleted_at'];
    protected $guarded = [
        'company_address_id',
        'settings',

        'address', // address polymorphic, avoid to save via "fill" method
    ];
    protected $casts = [
        'company_category_id' => 'company_category_id',
        'company_address_id' => 'company_address_id',
        'social_networks' => 'array',
    ];


    // polymorphic relation: addresses of company
    public function addresses(){
        return $this->morphMany('App\Models\Address', 'polymorphic');
    }

    public function address(){
        return $this->addresses()->first();
    }

    public function hasAddress(){
        return ($this->addresses()->count()) ? true : false;
    }

    // relation: company profiles
    public function profiles(){
        return $this->hasMany('App\Models\UserProfile');
    }

    // relation: company category
    public function category(){
        return $this->belongsTo('App\Models\CompanyCategory', 'company_category_id');
    }


    // non relational methods
    public function _data(){
        $data = $this;
        $data->address = ($this->hasAddress()) ? $this->address() : null;
        $data->category = $this->category;
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){
        if($this->hasAddress()){
            foreach($this->addresses as $address){
                $address->forceDelete();
                $address->_afterDelete();
            }
        }
    }

}
