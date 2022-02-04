<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\UserProfile;
use App\Models\UserRole;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Validation\Rule;

class UsersCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'name';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        if($order == 'name') {
            // order by field in related table
            $query = User::join('user_profiles as up', 'up.user_id', '=', 'users.id')
                ->orderBy('up.full_name', $direction)
                ->select('users.*');
        }else{
            $query = User::orderBy($order, $direction);
        }

        if($request->withProfile){
            $query->with('profile');
        }

        if($request->withHotel){
            $query->with('profile.hotel');
        }

        if($request->withCompany){
            $query->with('profile.company');
        }

        if($request->withRole){
            $query->with('role');
        }

        if($request->withUnreadChatMessages){
            $query->withCount(['unreadChatMessages' => function ($q) {
                $q->where('user_receiver_id', Auth::id());
                $q->where(function($q2){
                    $q2->where('viewed', '');
                    $q2->orWhere('viewed', null);
                });
            }]);
        }

        if($request->filter){
            $query->where(function($q) use ($request) {
                $q->where('email', 'like', '%'.$request->filter.'%');
                $q->orWhereHas('profile', function ($q2) use ($request) {
                    $q2->where('name', 'LIKE', '%' . $request->filter . '%');
                    $q2->orWhere('lastname', 'LIKE', '%' . $request->filter . '%');
                });
            });
        }

        if($request->trash){
            $query->onlyTrashed();
        }else if($request->withTrashed){
            $query->withTrashed();
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function getCurrent(){
        $user = \Auth::user();
        $res = $user;
        $res->role = $user->role;
        $res->permissions = $user->permissions;
        $res->profile = $user->profile;
        return $res;
    }

    public function get(User $user){
        return $user->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(User $user, Request $request){
        return $this->saveData($user, $request)->_data();
    }

    public function delete($id, Request $request){
        $user = User::withTrashed()->find($id);
        if($user){
            $denials = $user->_deleteAllowed();
            if(!count($denials)) {
                if($request->forceDelete){
                    $user->forceDelete();
                    $user->_afterDelete();
                }else{
                    $user->delete();
                }
            }else{
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            }

            return $user->_data();
        }
        return array();
    }

    public function restore($id){
        $user = User::withTrashed()->where('id', $id)->first();
        if($user){
            $user->restore();
            return $user->_data();
        }
        return array();
    }

    public function getDirectory(Request $request){
        $query = User::with('profile.hotel');
        $query->with('profile.company');
        $query->with('role');
        $s = ($request->search) ? trim($request->search) : '';

        $query->whereHas('profile', function($q) use ($s){
            $q->where('is_directory_enabled', 1);

            if($s != ''){
                $q->where(function($q2) use($s){
                    $q2->where('name', 'like', '%'.$s.'%');
                    $q2->orWhere('lastname', 'like', '%'.$s.'%');
                    $q2->orWhere('full_name', 'like', '%'.$s.'%');
                    $q2->oRwhere('job_title', 'like', '%'.$s.'%');
                    $q2->oRwhere('phone', 'like', '%'.$s.'%');

                    $q2->orWhereHas('hotel', function($q3) use($s){
                        $q3->where('name', 'like', '%'.$s.'%');
                    });

                    $q2->orWhereHas('company', function($q3) use($s){
                        $q3->where('name', 'like', '%'.$s.'%');
                    });
                });

            }
        });

        if($s != ''){
            $query->orWhere(function($q) use ($s){
                $q->whereHas('profile', function($q2) use ($s){
                    $q2->where('is_directory_enabled', 1);
                });
                $q->where('email', 'like', '%' . $s . '%');
            });
        }

        return $query->get();
    }

    public function getAssignableUsersForTask(){
        $query = User::with('profile.hotel');
        $query->with('profile.company');
        $query->with('role');

        $user = Auth::user();

        if($user->role->slug == 'regular'){

            // get only users from hotel or company
            $query->whereHas('profile', function($q) use ($user){
                if($user->profile->is_external){
                    $q->where('company_id', '=', $user->profile->company_id);
                }else{
                    $q->where('hotel_id', '=', $user->profile->hotel_id);
                }
            });

        }

        return $query->get();
    }

    private function saveData($user, $request){
        if(!$user){
            $user = new User;
        }
        $this->validateData($user, $request);

        $user->email = $request->email;

        // password
        if(!$user->id || ($user->id && $request->settings['update_password']) ){
            $user->password = Hash::make($request->password);
        }

        // role assignation
        if($request->user_role_id){
            $role = UserRole::find($request->user_role_id);
            if($role && $role->slug != 'super'){
                $user->user_role_id = $request->user_role_id;
            }
        }

        // protects super admin from disabling
        if($user->role && $user->role->slug != 'super'){
            $user->is_enabled = $request->is_enabled;
        }

        $user->save();

        // user permissions
        if($request->permissions && is_array($request->permissions)){
            $user_permissions = [];
            if( ! ($request->user_role_id == 1 || $request->user_role_id == 2) ) {
                foreach ($request->permissions as $permission) {
                    $user_permissions[] = $permission['id'];
                }
            }
            $user->permissions()->sync($user_permissions);
        }

        // creates the first profile for user on creating new user
        $profile = UserProfile::where('user_id', $user->id)->first();
        if(!$profile){
            $profile = new UserProfile;
            $profile->user_id = $user->id;
        }

        $profile->fill($request->profile);
        $profile->full_name = $profile->name . ' ' . $profile->lastname;

        // when send from profile, the index "is_external" doesn't exists
        if( isset($request->profile['is_external']) ){
            if($request->profile['is_external']){
                $profile['hotel_id'] = 0;
            }else{
                $profile['company_id'] = 0;
            }
        }

        $profile->save();

        Address::_save_polymorphic( $profile, 'profile_address_id', $request->profile['address'] );

        return $user;
    }

    private function validateData($user, $request){
        $rules = [
            'email' => ['required', 'email'],
        ];

        if($user->id){
            $rules['email'][] = Rule::unique('users')->ignore($user->id);
        }else{
            $rules['email'][] = Rule::unique('users');
        }

        if(!$user->id || ($user->id && $request->settings['update_password']) ){
            $rules['password'] = 'required|min:6|max:30';
        }

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }
    }

}
