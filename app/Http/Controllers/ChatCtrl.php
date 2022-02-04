<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;

class ChatCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }


    public function get(User $from_user, $limit = ''){
        $user = Auth::user();

        $query = Chat
            ::where( function($q) use ($user, $from_user){
                $q->where('user_sender_id', $user->id);
                $q->where('user_receiver_id', $from_user->id);
            })
            ->orWhere( function($q) use ($user, $from_user){
                $q->where('user_sender_id', $from_user->id);
                $q->where('user_receiver_id', $user->id);
            });

        $query->orderBy('created_at', 'desc');

        Chat::where( function($q) use ($user, $from_user){
            $q->where('user_sender_id', $from_user->id);
            $q->where('user_receiver_id', $user->id);
        })->update([
            'viewed' => 1
        ]);

        if($limit){
            $query->limit(100);
        }

        return $query->get()->reverse()->values();
    }

    public function send(User $to_user, Request $request){
        $message = trim($request->message);

        if($message != ''){
            $chat = new Chat;
            $chat->user_sender_id = Auth::id();
            $chat->user_receiver_id = $to_user->id;
            $chat->message = $request->message;
            $chat->save();
        }
    }

    public function getUnreadMessagesCount(){
        $user = Auth::user();

        $unreadMessages = DB::table('chat')
            ->selectRaw('user_sender_id as user_sender')
            ->where(function ($q) {
                $q->where('viewed', '');
                $q->orWhere('viewed', null);
            })
            ->where('user_receiver_id', $user->id)
            ->groupBy('user_sender')
            ->get();

        return count($unreadMessages);
    }
}
