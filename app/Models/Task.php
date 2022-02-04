<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{

    protected $table = 'tasks';
    public $timestamps = true;
    protected $guarded = [
        'owner_user_id',
    ];
    protected $casts = [
        'task_status_id' => 'integer',
        'owner_user_id' => 'integer',
        'assigned_user_id' => 'integer',
        'is_pinned_to_calendar' => 'integer',
    ];

    // relation: task statuses
    public function status(){
        return $this->belongsTo('App\Models\TaskStatus', 'task_status_id');
    }

    // relation: owner of a task
    public function userOwner(){
        return $this->belongsTo('App\Models\User', 'owner_user_id');
    }

    // relation: user assigned to task
    public function userAssigned(){
        return $this->belongsTo('App\Models\User', 'assigned_user_id');
    }



    // non relational methods

    public function _data(){
        $data = $this;
        $data->userOwner = $this->userOwner()->with('profile')->first();
        $data->userAssigned = $this->userAssigned()->with('profile')->first();
        $data->status = $this->status;
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];

        // user can delete is own tasks

        // admin can delete tasks from other members

        // corporative can delete tasks from other members

        return $denials;
    }

    public function _afterDelete(){}

}
