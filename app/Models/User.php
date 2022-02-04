<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'email',
        'password',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'user_role_id' => 'integer',
        'valid_email' => 'integer',
        'is_enabled' => 'integer',
    ];

    // relation: user has profile
    public function profile(){
        return $this->hasOne('App\Models\UserProfile');
    }

    // relation: user has role
    public function role(){
        return $this->belongsTo('App\Models\UserRole', 'user_role_id');
    }

    // relation: permission to internal sections
    public function permissions(){
        return $this->belongsToMany('App\Models\UserPermission', 'user_has_permissions');
    }

    // relation: tasks owned by user
    public function tasks(){
        return $this->hasMany('App\Models\Task', 'owner_user_id');
    }

    // relation: tasks assigned to user (can be assigned by himself or another user)
    public function tasksAssigned(){
        return $this->hasMany('App\Models\Task', 'assigned_user_id');
    }

    // relation: events created by user
    public function events(){
        return $this->hasMany('App\Models\Event');
    }

    // relation: shared folders
    public function foldersOwned(){
        return $this->hasMany('App\Models\Folder', 'user_owner_id');
    }

    // relation: assurance visits
    public function qualityAssuranceVisits(){
        return $this->hasMany('App\Models\QualityAssuranceVisit', 'owner_user_id');
    }

    // relation: extension requests
    public function qualityVisitExtensionRequests(){
        return $this->hasMany('App\Models\QualityVisitExtensionRequest', 'owner_user_id');
    }

    // relation: verified extension requests
    public function verifiedQualityVisitExtensionRequests(){
        return $this->hasMany('App\Models\QualityVisitExtensionRequest', 'verifier_user_id');
    }

    // relation: exension requests
    public function qualityVisitExensionRequests(){
        return $this->hasMany('App\Models\QualityVisitExensionRequest', 'owner_user_id');
    }

    // relation: verified exension requests
    public function verifiedQualityVisitExensionRequests(){
        return $this->hasMany('App\Models\QualityVisitExensionRequest', 'verifier_user_id');
    }

    // relation: folders user has access
    public function foldersPermitted(){
        return $this->belongsToMany('App\Models\Folder', 'folder_permissions');
    }

    // relation: notifications for the user
    public function notifications(){
        return $this->hasMany('App\Models\Notification', 'user_id');
    }

    // relation: user has chat messages
    public function chat(){
        return $this->hasMany('App\Models\Chat', 'user_sender_id');
    }
    public function unreadChatMessages(){
        return $this->chat();
    }

    // non relational methods

    public function _data(){
        $data = $this;
        $data->permissions = $this->permissions;
        $data->profile = $this->profile->_data();
        $data->role = $this->role;
        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];

        // super admin cant be deleted
        if($this->isSuper()){
            $denials[] = __('messages.super-admin-delete-restriction');
        }

        // only admin can delete or regular with permissions
        if( !$this->isAdmin() && 'verify-if-have-permission-for-user-section' && 0){ // TODO
            $denials[] = __('messages.cant-delete-users');
        }

        return $denials;
    }

    public function _afterDelete(){
        $this->profile->delete();
        $this->profile->_afterDelete();

        $this->permissions()->detach();

        if($this->tasks()->count()){
            foreach($this->tasks as $task){
                $task->delete();
                $task->_afterDelete();
            }
        }

        if($this->events()->count()){
            foreach($this->events as $event){
                $event->delete();
                $event->_afterDelete();
            }
        }

        if($this->notifications()->count()){
            foreach($this->notifications as $notification){
                $notification->delete();
                $notification->_afterDelete();
            }
        }
    }

    public function _updateFolderPermissions($userOptions){
        if(is_array($userOptions) && count($userOptions)){
            foreach($userOptions as $userOption){
                if($userOption['is_permitted']){

                }else{
                    
                }
            }
        }
    }

    public function isSuper(){  return ($this->role->slug == 'super') ? true : false; }
    public function isAdmin(){  return ($this->role->slug == 'super' || $this->role->slug == 'admin') ? true : false; }
    public function isCorporative(){  return ($this->role->slug == 'corporative') ? true : false; }
    public function isRegular(){  return ($this->role->slug == 'regular') ? true : false; }

}
