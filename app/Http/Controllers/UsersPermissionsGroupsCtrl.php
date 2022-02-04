<?php

namespace App\Http\Controllers;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\UserPermissionsGroup;
use Auth;

class UsersPermissionsGroupsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'name';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = UserPermissionsGroup::orderBy($order, $direction);

        if($request->filter){
            $query->where('name', 'like', '%'.$request->filter.'%');
            $query->orWhere('slug', 'like', '%'.$request->filter.'%');
        }

        if($request->withPermissions){
            $query->with(['permissions' => function($q) {
                $q->orderBy('name', 'asc');
            }]);
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(UserPermissionsGroup $group){
        return $group->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(UserPermissionsGroup $group, Request $request){
        return $this->saveData($group, $request)->_data();
    }

    public function delete($id){
        if(!Auth::user()->isAdmin()){
            throw new HttpResponseException(
                response()->json(['errors'=>[__('messages.admin-delete-permission-group-restriction')]], 400)
            );
        }

        $group = UserPermissionsGroup::find($id);
        if ($group) {
            $denials = $group->_deleteAllowed();
            if (count($denials)) {
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            } else {
                $group->delete();
                $group->_afterDelete();
            }
            return $group->_data();
        }
    }

    private function saveData($group, $request){
        if(!$group){
            $group = new UserPermissionsGroup;
        }
        $this->validateData($request);

        $group->fill($request->all());
        $group->save();

        return $group;
    }

    private function validateData($request){
        if(!Auth::user()->isAdmin()){
            throw new HttpResponseException(
                response()->json(['errors'=>[__('messages.admin-actions-permission-group-restriction')]], 400)
            );
        }

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
