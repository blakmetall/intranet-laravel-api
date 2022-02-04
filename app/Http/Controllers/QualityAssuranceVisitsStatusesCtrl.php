<?php

namespace App\Http\Controllers;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\QualityAssuranceVisitStatus;

class QualityAssuranceVisitsStatusesCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'name';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = QualityAssuranceVisitStatus::orderBy($order, $direction);

        if($request->filter){
            $query->where('name', 'like', '%'.$request->filter.'%');
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(QualityAssuranceVisitStatus $category){
        return $category->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(QualityAssuranceVisitStatus $category, Request $request){
        return $this->saveData($category, $request)->_data();
    }

    public function delete($id){
        $category = QualityAssuranceVisitStatus::find($id);
        if ($category) {
            $denials = $category->_deleteAllowed();
            if (count($denials)) {
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            } else {
                $category->delete();
                $category->_afterDelete();
            }
            return $category->_data();
        }
    }


    private function saveData($category, $request){
        if(!$category){
            $category = new QualityAssuranceVisitStatus;
        }
        $this->validateData($request);

        $category->fill($request->all());
        $category->save();

        return $category;
    }


    private function validateData($request){
        $rules = [
            'name' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }
    }
}
