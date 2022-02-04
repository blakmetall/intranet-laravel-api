<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityAssuranceVisit extends Model
{

    protected $table = 'quality_assurance_visits';
    public $timestamps = true;
    protected $guarded = [
        'owner_user_id',
        'notification_file_id',
        'report_file_id',

        'date',
        'time'
    ];
    protected $casts = [
        'quality_assurance_visit_category_id' => 'integer',
        'hotel_id' => 'integer',
        'owner_user_id' => 'integer',
        'revision_number' => 'integer',
        'score' => 'double',
        'notification_file_id' => 'integer',
        'report_file_id' => 'integer',
    ];
    public $folder_name = 'quality_assurance_visits';

    // polymorphic relation: files
    public function files()
    {
        return $this->morphMany('App\Models\File', 'polymorphic');
    }

    // relation: notification file
    public function notificationFile(){
        return $this->morphMany('App\Models\File', 'polymorphic')
            ->where('input_id', 'notification_file');
    }

    // relation: report file
    public function reportFile(){
        return $this->morphMany('App\Models\File', 'polymorphic')
            ->where('input_id', 'report_file');
    }

    // relation: hotel assigned to visit
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    // relation: status assigned to visit
    public function status()
    {
        return $this->belongsTo('App\Models\QualityAssuranceVisitStatus', 'quality_assurance_visit_category_id');
    }

    // relation: user owner of this visit
    public function userOwner()
    {
        return $this->belongsTo('App\Models\User', 'owner_user_id');
    }

    // relation: extension request using this file
    public function visitExtensionRequests()
    {
        return $this->hasMany('App\Models\QualityVisitExtensionRequest');
    }


    // non relational methods
    public function _data(){
        $data = $this;
        $data->hotel = $this->hotel;
        $data->status = $this->status;
        $data->userOwner = $this->userOwner()->with('profile')->first();

        // notification_file
        $data->notification_file = $this->notificationFile()->first();
        if($data->notification_file && $data->notification_file->is_image){
            $data->notification_file->media = MediaSize::_get($data->notification_file);
        }

        // presentation file img
        $data->report_file = $data->reportFile()->first();
        if($data->report_file && $data->report_file->is_image){
            $data->report_file->media = MediaSize::_get($data->report_file);
        }

        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];
        return $denials;
    }

    public function _afterDelete(){

        // remove files directly attached to this record
        if($this->files()->count()){
            foreach($this->files as $file){
                $file->_removeFiles();
                $file->forceDelete();
                $file->_afterDelete();
            }
        }

        // delete extension request related
        if( $this->visitExtensionRequests()->count() ){
            foreach($this->visitExtensionRequests as $extension_request){
                $extension_request->delete();
                $extension_request->_afterDelete();
            }
        }

    }


}
