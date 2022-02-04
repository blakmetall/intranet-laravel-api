<?php

namespace App\Http\Controllers;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\BrandsiteSectionFeature;

class BrandsiteSectionsFeaturesCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'name_es';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = BrandsiteSectionFeature::orderBy($order, $direction);

        if($request->filter){
            $query->where('name_es', 'like', '%'.$request->filter.'%');
            $query->orWhere('name_en', 'like', '%'.$request->filter.'%');
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(BrandsiteSectionFeature $feature){
        return $feature->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(BrandsiteSectionFeature $feature, Request $request){
        return $this->saveData($feature, $request)->_data();
    }

    public function delete($id){
        $feature = BrandsiteSectionFeature::find($id);
        if($feature){
            $denials = $feature->_deleteAllowed();
            if(count($denials)){
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            }else {
                $feature->delete();
                $feature->_afterDelete();
            }
            return $feature->_data();
        }
        return array();
    }


    private function saveData($feature, $request){
        if(!$feature){
            $feature = new BrandsiteSectionFeature;
        }
        $this->validateData($request);

        $feature->fill($request->all());
        $feature->save();

        return $feature;
    }


    private function validateData($request){
        $rules = [
            'brandsite_section_id' => ['required'],
            'name_es' => ['required'],
            'name_en' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }
    }
}
