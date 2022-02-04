<?php

namespace App\Http\Controllers;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\UserPermission;
use Auth;

class UsersPermissionsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'name';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = UserPermission::orderBy($order, $direction);

        if($request->filter){
            $query->where('name', 'like', '%'.$request->filter.'%');
        }

        if($request->user_permissions_group_id){
            $query->where('user_permissions_group_id', $request->user_permissions_group_id);
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(UserPermission $permission){
        return $permission->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(UserPermission $permission, Request $request){
        return $this->saveData($permission, $request)->_data();
    }

    public function delete($id){
        if(!Auth::user()->isAdmin()){
            throw new HttpResponseException(
                response()->json(['errors'=>[__('messages.admin-delete-permission-restriction')]], 400)
            );
        }

        $permission = UserPermission::find($id);
        if ($permission) {
            $denials = $permission->_deleteAllowed();
            if (count($denials)) {
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            } else {
                $permission->delete();
                $permission->_afterDelete();
            }
            return $permission->_data();
        }
    }

    private function saveData($permission, $request){
        if(!$permission){
            $permission = new UserPermission;
        }
        $this->validateData($request);

        $permission->fill($request->all());
        $permission->save();

        return $permission;
    }

    private function validateData($request){

        if(!Auth::user()->isAdmin()){
            throw new HttpResponseException(
                response()->json(['errors'=>[__('messages.admin-actions-permission-restriction')]], 400)
            );
        }

        $rules = [
            'user_permissions_group_id' => ['required'],
            'name' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }

        // avoid entry to permissions route
        if($request->slug == '/admin/permissions-groups'){
            throw new HttpResponseException(
                response()->json(['errors'=>'ROUTE_NOT_ALLOWED'], 400)
            );
        }
    }
}
