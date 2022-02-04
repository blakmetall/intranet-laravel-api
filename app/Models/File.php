<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Storage;

class File extends Model
{
    use SoftDeletes, Sluggable;

    protected $table = 'files';
    public $timestamps = true;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'is_featured',
        'input_id'
    ];
    protected $casts = [
        'polymorphic_id' => 'integer',
        'size_bytes' => 'double',
        'is_featured' => 'integer',
        'flip_page_enabled' => 'integer'
    ];

    public function sluggable(){
        return [
            'slug' => [
                'source' => ['name', 'version']
            ]
        ];
    }


    // polymorphic relation: folder has many files
    public function files(){
        return $this->morphMany('App\Models\File', 'polymorphic');
    }

    // relation: file has sizes (apply only to type images)
    public function sizes(){
        return $this->belongsToMany('App\Models\MediaSize', 'files_has_media_sizes')
            ->withPivot(['filepath', 'url']);
    }

    // relation: folder owner of the file
    public function folder(){
        return $this->belongsTo('App\Models\Folder', 'polymorphic_id');
    }

    // relation: features linked to current file
    public function features(){
        return $this->belongsToMany('App\Models\BrandsiteSectionFeature', 'file_has_features', 'file_id', 'brandsite_section_feature_id');
    }

    public function _removeFiles(){
        MediaSize::_removeMedia($this);
        $this->_removeOriginalFile($this->filepath);
    }

    public function _afterDelete(){
        $this->sizes()->detach();
        $this->features()->detach();
    }

    public function _generateMediaSizes(){
        MediaSize::_generate($this);
    }

    public function _updateFeatures($features){
        if($features && is_array($features)){
            $data = [];
            foreach($features as $feature){
                if($feature['enabled']){
                    $data[ $feature['id'] ] = [];
                }
            }
            $this->features()->sync($data);
        }
    }

    // Returns the model to be used based on polymorphic_type string
    public static function _getPolymorphicModel($polymorphic_type){
        switch($polymorphic_type){
            case 'user':
                return new User;
                break;
            case 'hotel':
                return new Hotel;
                break;
            case 'quality-assurance-visit':
                return new QualityAssuranceVisit;
                break;
            case 'quality-extension-request':
                return new QualityVisitExtensionRequest;
                break;
            case 'quality-exension-request':
                return new QualityVisitExensionRequest;
                break;
            case 'mahgazine-edition':
                return new MahgazineEdition;
                break;
            case 'mahgazine-article':
                return new MahgazineArticle;
                break;

        }

        return false;
    }

    public static function _removeExtension($filename){
        return substr($filename, 0 , (strrpos($filename, ".")));
    }

    private function _removeOriginalFile($filepath){
        if(file_exists(Storage::path($filepath))){
            @unlink(Storage::path($filepath));
        }
    }



}
