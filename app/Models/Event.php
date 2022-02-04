<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Event extends Model
{
    use Sluggable;

    protected $table = 'events_calendar';
    public $timestamps = true;
    protected $guarded = [
        'user_id',
        'slug',

        'start_datetime',
        'start_date',
        'start_time',

        'end_datetime',
        'end_date',
        'end_time'
    ];
    protected $casts = [
        'events_calendar_category_id' => 'integer',
        'user_id' => 'integer',
        'hotel_id' => 'integer',
        'is_private' => 'integer',
        'is_finished' => 'integer',
    ];

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    // relation: task statuses
    public function category(){
        return $this->belongsTo('App\Models\EventCategory', 'events_calendar_category_id');
    }

    // relation: owner of a event
    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    // relation: hotel assigned for the event (optional)
    public function hotel(){
        return $this->belongsTo('App\Models\Hotel');
    }

    public function _data(){
        $data = $this;
        $data->category = ($this->category()->count()) ? $this->category->_data() : null;
        $data->hotel = $this->hotel;
        $data->user = $this->user()->with('profile')->first();
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){}
}
