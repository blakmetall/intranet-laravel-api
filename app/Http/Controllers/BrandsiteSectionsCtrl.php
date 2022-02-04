<?php

namespace App\Http\Controllers;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\BrandsiteSection;

class BrandsiteSectionsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'name_es';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = BrandsiteSection::orderBy($order, $direction);

        $query->with('features'); // load BrandsiteSectionFeatures

        if($request->filter){
            $filter = $request->filter;

            $query->where('name_es', 'like', '%'.$filter.'%');
            $query->orWhere('name_en', 'like', '%'.$filter.'%');


            $query->orWhereHas('features', function($q) use($filter) {
                $q->where('name_es', 'like', '%'.$filter.'%');
                $q->orWhere('name_en', 'like', '%'.$filter.'%');
            });
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(BrandsiteSection $section){
        return $section->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(BrandsiteSection $section, Request $request){
        return $this->saveData($section, $request)->_data();
    }

    public function delete($id){
        $section = BrandsiteSection::find($id);
        if($section){
            $denials = $section->_deleteAllowed();
            if(count($denials)){
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            }else {
                $section->delete();
                $section->_afterDelete();
            }

            return $section->_data();
        }
        return array();
    }

    /** Private helpers */

    private function saveData($section, $request){
        if(!$section){
            $section = new BrandsiteSection;
        }
        $this->validateData($section, $request);

        $section->fill($request->all());
        $section->save();

        $section->_saveFeatures($request->features);

        return $section;
    }


    private function validateData($section, $request){

        if($section->id && $section->is_predefined){ // only apply validation on not predefined sections
            return 0;
        }

        $rules = [
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
