<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{

    protected $table = 'states';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'country_id' => 'integer',
    ];

    // relation: addresses of country
    public function addresses(){
        return $this->hasMany('App\Models\Address');
    }

    // relation: addresses of country
    public function country(){
        return $this->belongsTo('App\Models\Country');
    }

}
