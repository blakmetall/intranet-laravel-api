<?php

namespace App\Http\Controllers;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\EventCategory;

class EventsCategoriesCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'name';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = EventCategory::orderBy($order, $direction);

        if($request->filter){
            $query->where('name', 'like', '%'.$request->filter.'%');
            $query->orWhere('color', 'like', '%'.$request->filter.'%');
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(EventCategory $category){
        return $category->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(EventCategory $category, Request $request){
        //return [$request, $category];
        return $this->saveData($category, $request)->_data();
    }

    public function delete($id){
        $category = EventCategory::find($id);
        if($category){
            $denials = $category->_deleteAllowed();
            if(count($denials)) {
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            }else{
                $category->delete();
                $category->_afterDelete();
            }
            return $category->_data();
        }
        return array();
    }


    private function saveData($category, $request){
        if(!$category){
            $category = new EventCategory;
        }

        $this->validateData($request);

        $category->fill($request->all());
        $category->save();

        return  $category;
    }


    private function validateData($request){
        $rules = [
            'name' => ['required'],
            'color' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }
    }
}
