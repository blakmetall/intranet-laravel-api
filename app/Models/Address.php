<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{

    protected $table = 'addresses';
    public $timestamps = true;
    protected $guarded = [
        'id',
        'polymorphic_id',
        'polymorphic_type',
    ];
    protected $casts = [
        'polymorphic_id' => 'integer',
        'country_id' => 'integer',
        'state_id' => 'integer',
        'zip' => 'integer',
    ];
    protected $hidden = [
        'polymorphic_type'
    ];

    // polymorphic relation: allows any other table to have addresses
    // used by: profile, hotels, companies, etc
    public function polymorphic(){
        return $this->morphTo();
    }

    // relation: state of the address
    public function state(){
        return $this->belongsTo('App\Models\State');
    }

    // relation: country of the address
    public function country(){
        return $this->belongsTo('App\Models\Country');
    }



    public function _data(){
        $data = $this;
        //$data->country = $this->country;
        //$data->state = $this->state;
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){}


    public static function _save_polymorphic($obj, $address_field_name_id, $address_request){
        // prepare and save address data
        // prepare and save address data
        if(!$obj->hasAddress()){
            $address = new Address;
        }else{
            $address = $obj->address();
        }

        $address->fill($address_request);
        $address->save();

        // link if unlinked address data
        if(!$obj->hasAddress()){
            $obj->$address_field_name_id = $address->id;
            $obj->save();

            // save polymrphic relation
            $obj->addresses()->save($address);
        }
    }
}
