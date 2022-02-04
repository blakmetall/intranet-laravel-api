<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class QualityVisitExensionRequest extends Model
{

    protected $table = 'quality_visit_exension_requests';
    public $timestamps = true;
    protected $guarded = [
        'owner_user_id',
        'document_file_id',
    ];
    protected $casts = [
        'hotel_id' => 'integer',
        'owner_user_id' => 'integer',
        'verifier_user_id' => 'integer',
        'document_file_id' => 'integer',
    ];
    public $folder_name = 'quality_exension_requests';

    // polymorphic relation: files
    public function files(){
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

    // relation: hotel of exension request
    public function hotel(){
        return $this->belongsTo('App\Models\Hotel', 'hotel_id');
    }

    // relation: user verifier of exension request
    public function userVerifier(){
        return $this->belongsTo('App\Models\User', 'verifier_user_id');
    }

    // relation: quality document assigned to exension request
    public function qualityDocument(){
        return $this->belongsTo('App\Models\QualityDocument');
    }

    // Exension request
    public function verify(QualityVisitExensionRequest $extensionRequest){

        $extensionRequest->_approve();

        $extensionRequest->assuranceVisit->_setExensionDate( $extensionRequest->extension_date );

        return $extensionRequest;
    }

    public function deny(QualityVisitExensionRequest $extensionRequest){
        $extensionRequest->_deny();
        return $extensionRequest;
    }


    // non relational methods

    public function _data(){
        $data = $this;
        $data->hotel = $this->hotel;
        $data->userOwner = $this->userOwner()->with('profile')->first();
        $data->userVerifier =$this->userVerifier()->with('profile')->first();

        // document file
        $data->document_file = $this->documentFile()->first();
        if($data->document_file && $data->document_file->is_image){
            $data->document_file->media = MediaSize::_get($data->document_file);
        }

        return $data;
    }

    public function _deleteAllowed(){
        $denials = [];

        /*if("admin, corporative can delete or _only_user_owner_can_delete"){
            $denials[] = "Solo el propietario puede eliminar ésta exensión";
        }*/

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
