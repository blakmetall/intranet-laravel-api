<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Auth;

class NotificationsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'created_at';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'desc';

        $query = Notification::orderBy($order, $direction);
        $query->where('user_id', Auth::id());

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function delete($id){
        $notification = Notification::where('id', $id)->where('user_id', Auth::id() )->first();
        if ($notification) {
            $denials = $notification->_deleteAllowed();
            if (count($denials)) {
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            } else {
                $notification->delete();
                $notification->_afterDelete();
            }
            return $notification->_data();
        }
    }

    public function deleteAll(){
        $notifications = Notification::where('user_id', Auth::id() )->get();
        if($notifications){
            foreach($notifications as $notification){
                $notification->delete();
                $notification->_afterDelete();
            }
        }
    }

    public function setViewedStatus($id){
        $notification = Notification::where('id', $id)->where('user_id', Auth::id() )->first();
        if($notification){
            $notification->viewed = 1;
            $notification->save();
        }
    }
}
