<?php

namespace App\Http\Controllers;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\UserProfile;

class UsersProfilesCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'name';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = UserProfile::orderBy($order, $direction);

        if($request->filter){
            $query->where('name', 'like', '%'.$request->filter.'%');
            $query->orWhere('lastname', 'like', '%'.$request->filter.'%');
            $query->orWhere('phone', 'like', '%'.$request->filter.'%');
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(UserProfile $profile){
        return $profile->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(UserProfile $profile, Request $request){
        return $this->saveData($profile, $request)->_data();
    }

    public function delete($id){
        $profile = UserProfile::find($id);
        if ($profile) {
            $denials = $profile->_deleteAllowed();
            if (count($denials)) {
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            } else {
                $profile->delete();
                $profile->_afterDelete();
            }
            return $profile->_data();
        }
    }


    private function saveData($profile, $request){
        if(!$profile){
            $profile = new UserProfile;
        }
        $this->validateData($request);

        $profile->fill($request->all());
        $profile->save();

        return $profile;
    }


    private function validateData($request){
        $rules = [
            'job_title' => ['required'],
            'name' => ['required'],
            'lastname' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }
    }
}
