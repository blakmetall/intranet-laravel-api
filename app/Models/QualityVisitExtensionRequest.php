<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class QualityVisitExtensionRequest extends Model
{

    protected $table = 'quality_visit_extension_requests';
    public $timestamps = true;
    protected $guarded = [
        'owner_user_id',
        'document_file_id',

        'date',
        'time'
    ];
    protected $casts = [
        'quality_assurance_visit_id' => 'integer',
        'hotel_id' => 'integer',
        'owner_user_id' => 'integer',
        'verifier_user_id' => 'integer',
        'document_file_id' => 'integer',
    ];
    public $folder_name = 'quality_extension_requests';

    // polymorphic relation: files
    public function files()
    {
        return $this->morphMany('App\Models\File', 'polymorphic');
    }

    // relation: document file
    public function documentFile(){
        return $this->morphMany('App\Models\File', 'polymorphic')
            ->where('input_id', 'document_file');
    }

    // relation: user owner of exension request
    public function userOwner(){
        return $this->belongsTo('App\Models\User', 'owner_user_id');
    }

    // relation: user verifier of exension request
    public function userVerifier(){
        return $this->belongsTo('App\Models\User', 'verifier_user_id');
    }

    // relation: quality document assigned to exension request
    public function qualityDocument(){
        return $this->belongsTo('App\Models\QualityDocument');
    }

    // relation: assurance visit assigned to current extension request
    public function assuranceVisit(){
        return $this->belongsTo('App\Models\QualityAssuranceVisit', 'quality_assurance_visit_id');
    }


    // non relational methods

    public function _data(){
        $data = $this;
        $data->assuranceVisit = $this->assuranceVisit()->with(['hotel', 'status', 'userOwner.profile'])->first();
        $data->userOwner = $this->userOwner()->with('profile')->first();
        $data->userVerifier = $this->userVerifier()->with('profile')->first();

        // document file
        $data->document_file = $this->documentFile()->first();
        if($data->document_file && $data->document_file->is_image){
            $data->document_file->media = MediaSize::_get($data->document_file);
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
    }

    public function _approve(){
        $this->verifier_user_id = Auth::id();
        $this->is_verified = 1;
        $this->is_approved = 1;
        $this->save();
    }

    public function _deny(){
        $this->verifier_user_id = Auth::id();
        $this->is_verified = 1;
        $this->is_approved = 0;
        $this->save();
    }
}
