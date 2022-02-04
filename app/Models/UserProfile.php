<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profiles';
    public $timestamps = false;
    protected $guarded = [
        'user_id',
        'profile_address_id',
        'profile_file_id',
        'full_name',

        'address', // do not attempt to fill address
    ];
    protected $casts = [
        'user_id' => 'integer',
        'is_external' => 'integer',
        'hotel_id' => 'integer',
        'company_id' => 'integer',
        'external_brandsite_enabled' => 'integer',
        'external_mahgazine_enabled' => 'integer',
        'is_directory_enabled' => 'integer',
        'profile_address_id' => 'integer',
        'profile_file_id' => 'integer',
        'use_local_timezone' => 'integer'
    ];
    public $folder_name = 'user_profiles';

    // polymorphic relation: addresses of user
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

    // relation: user owner of profile
    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    // relation: hotel owner of profile
    public function hotel(){
        return $this->belongsTo('App\Models\Hotel');
    }

    // relation: company owner of profile
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }

    // relation: group that owns this permission
    public function group(){
        return $this->belongsTo('App\Models\UserPermissionsGroup');
    }

    // relation: photo of profile
    public function photo(){
        return $this->hasOne('App\Models\File', 'profile_file_id');
    }

    // non relational methods

    public function _data(){
        $data = $this;
        $data->address = ($this->hasAddress()) ? $this->address() : null;
        //$data->photo = $this->photo;
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];

        // avoid super admin to be deleted

        return $denials;
    }

    public function _afterDelete(){
        if($this->files()->count()){
            foreach($this->files as $file){
                $file->forceDelete();
                $file->_afterDelete();
            }
        }

        if($this->hasAddress()){
            foreach($this->addresses as $address){
                $address->forceDelete();
                $address->_afterDelete();
            }
        }
    }
}
