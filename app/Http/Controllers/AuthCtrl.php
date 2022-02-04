<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Mail\PasswordUpdated;
use App\Mail\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class AuthCtrl extends Controller
{

    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }
    
    /**
     * Login attemt
     * @param Request $request (email, password)
     * @return array (status of login attempt)
     */
    public function login(Request $request)
    {
        $res = ['success' => false];
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'is_enabled' => 1])) {
            $user = Auth::user();
            $res['success'] = true;
            $res['token'] = $user->createToken('passportToken')->accessToken;
            $res['user'] = $user;
            $res['role'] = $user->role;
            $res['permissions'] = $user->permissions;
            //$res['config'] = $user->config;
        }
        return $res;
    }

    // passport token logout
    public function logout(){
        if(Auth::check()){
            Auth::user()->AauthAcessToken()->delete();
        }
    }

    /**
     * Unlock attempt
     * @param Request $request (email, password)
     * @return array (status of unlock attempt)
     */
    /*public function unlock(Request $request)
    {
        $res = ['success' => false];
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $res['success'] = true;
        }
        return $res;
    }*/

    /**
     * Checks valid email or password to send request recovery through email
     * @param Request $request (email or username)
     * @return array (status)
     */
    public function requestRecovery(Request $request)
    {
        $res = ['success' => false];
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $res['success'] = true;
            $user->recovery_key = str_random(60);
            $user->save();

            Mail::to($user->email)->send(new PasswordReset($user));
        }

        return $res;
    }

    /**
     * Checks if a recovery_key sent through URL is valid. This, for the user to reset their password
     * @param $recovery_key
     * @return array (status)
     */
    public function verifyRecoveryKey($recovery_key){
        $res = ['success' => false];

        $user = User::where('recovery_key', $recovery_key)->first();
        if($user){
            $res['success'] = true;
            $res['user'] = $user;
        }

        return $res;
    }


    /**
     * Reset password attempt
     * @param Request $request (password, recovery_key)
     * @return array (status)
     */
    public function resetPassword(Request $request)
    {
        // simple validation
        $rules['password'] = 'required|min:6|max:30';
        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 400);
        }

        $res = ['success' => false];
        $user = User::where('id', $request->id)->where('recovery_key', $request->recovery_key)->first();
        if ($user) {
            $res['success'] = true;
            $user->password = bcrypt($request->password);
            $user->recovery_key = '';
            $user->save();

            Mail::to($user->email)->send(new PasswordUpdated($user));
        }

        return $res;
    }
}
