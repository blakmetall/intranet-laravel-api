<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    use Sluggable;

    protected $table = 'events_calendar_categories';
    public $timestamps = false;
    protected $guarded = [];

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    // relation: events with current type
    public function events(){
        return $this->hasMany('App\Models\Event', 'events_calendar_category_id');
    }

    public function _data(){
        return $this;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){

        // set category to 0 to registered events with current category deleted
        Event::where('events_calendar_category_id', $this->id)
            ->update(['events_calendar_category_id' => 0]);
    }

}
