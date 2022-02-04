<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

    protected $table = 'notifications';
    public $timestamps = true;
    protected $guarded = [
        'user_id',
    ];
    protected $casts = [
        'user_id' => 'integer',
    ];

    // relation: task statuses
    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id');
    }


    public function _data(){
        $data = $this;
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){}

}
