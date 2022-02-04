<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class TaskStatus extends Model
{

    use Sluggable;

    protected $table = 'tasks_status';
    public $timestamps = false;
    protected $guarded = [
        'slug',
    ];

    public function sluggable(){
        return [
            'slug' => [
                'source' => 'slug'
            ]
        ];
    }

    // relation: task with current status
    public function tasks(){
        return $this->belongsTo('App\Models\Task');
    }

}
