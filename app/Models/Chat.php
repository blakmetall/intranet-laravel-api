<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{

    protected $table = 'chat';
    public $timestamps = true;
    protected $guarded = [
        'user_sender_id',
        'user_receiver_id',
        'viewed'
    ];
    protected $casts = [
        'user_sender_id' => 'integer',
        'user_receiver_id' => 'integer',
    ];

    // relation: task statuses
    public function sender(){
        return $this->belongsTo('App\Models\User', 'user_sender_id');
    }

    public function receiver(){
        return $this->belongsTo('App\Models\User', 'user_receiver_id');
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
