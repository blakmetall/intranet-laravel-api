<?php

namespace App\Http\Controllers;


use App\Models\Notification;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;
use Auth;

class EventsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'name';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = Event::orderBy($order, $direction);
        $query->with('category');

        $user = Auth::user();

        // show only
        $query->where(function($q) use ($user) {

            // private events
            $q->where(function($q2) use ($user) {
                $q2->where('is_private', 1);
                $q2->where('user_id', $user->id);
            });

            // non private events
            if( $user->isAdmin() ){
                $q->orWhere('is_private', 0);
            }else{

                // all events for all hotels
                $q->orWhere(function($q3) use ($user) {
                    $q3->where('is_private', 0);
                    $q3->where('hotel_id', 0);
                });

                // all events for specific user hotel
                $q->orWhere(function($q4) use ($user) {
                    $user_hotel_id = ($user->profile->hotel) ? $user->profile->hotel->id : -1;
                    $q4->where('is_private', 0);
                    $q4->where('hotel_id', $user_hotel_id);
                });
            }

        });


        // filter by string
        if($request->filter){
            $query->where(function($q) use($request){
                $q->where('name', 'like', '%'.$request->filter.'%');
                $q->orWhere('description', 'like', '%'.$request->filter.'%');
            });
        }

        // filter by date range ( prepare to filter using user timezone)
        if($request->start_date && $request->end_date){
            $query->where(function($q) use ($request) {
                $format = 'Y-m-d H:i:s';

                // prepare timezone
                $profile = Auth::user()->profile;
                if($profile->use_local_timezone){
                    $timezone = $request->header('APP_TIMEZONE');
                }else{
                    $timezone = $profile->timezone;
                }

                $start_date = $request->start_date . ' 00:00:00';
                $end_date = $request->end_date . ' 23:59:59';

                $start_date = Carbon::createFromFormat($format, $start_date, $timezone)
                    ->setTimezone('UTC')->format('Y-m-d H:i:s');

                $end_date = Carbon::createFromFormat($format, $end_date, $timezone)
                    ->setTimezone('UTC')->format('Y-m-d H:i:s');


                $q->whereBetween('start_datetime', [$start_date, $end_date]);
                $q->orWhereBetween('end_datetime', [$start_date, $end_date]);
            });
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(Event $event){
        return $event->_data();
    }

    public function store(Request $request){
       return $this->saveData(false, $request)->_data();
    }

    public function edit(Event $event, Request $request){
        return $this->saveData($event, $request)->_data();
    }

    public function delete($id){
        $event = Event::find($id);
        if($event){
            $denials = $event->_deleteAllowed();
            if(count($denials)){
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            }else{
                $event->delete();
                $event->_afterDelete();
            }
            return $event->_data();
        }
        return array();
    }

    private function saveData($event, $request){
        $notify = false;
        if(!$event){
            $event = new Event;
            $event->user_id = Auth::id();
            $notify = true;
        }

        $this->validateData($request);

        $event->fill($request->all());

        $profile = Auth::user()->profile;
        $format = 'Y-m-d H:i:s';
        $timezone = ($profile->use_local_timezone) ? $request->header('APP_TIMEZONE') : $profile->timezone;

        // start date
        $start_date = Carbon::parse($request->start_date, 'UTC');
        $start_date_with_time_string = $start_date->format('Y-m-d') . ' ' . $request->start_time . ':00';
        $start_datetime = Carbon::createFromFormat($format, $start_date_with_time_string, $timezone);
        $event->start_datetime = $start_datetime->setTimezone('UTC')->format($format);

        // end date
        $end_date = Carbon::parse($request->end_date, 'UTC');
        $end_date_with_time_string = $end_date->format('Y-m-d') . ' ' . $request->end_time . ':00';
        $end_datetime = Carbon::createFromFormat($format, $end_date_with_time_string, $timezone);
        $event->end_datetime = $end_datetime->setTimezone('UTC')->format($format);

        $event->save();


        if($notify && !$event->is_private){
            if($event->hotel_id){
                $users = User::whereHas('profile', function($q) use ($event){
                    $q->where('hotel_id', $event->hotel_id);
                })->get();
            }else{
                $users = User::all();
            }

            if($users){
                foreach($users as $user){
                    $notification = New Notification;
                    $notification->user_id = $user->id;
                    $notification->type = 'event';
                    $notification->title = 'N_NEW_EVENT';
                    $notification->url = '/calendar/view/' . $event->id;
                    $notification->save();
                }
            }
        }

        return $event;
    }

    private function validateData($request){
        $rules = [
            'events_calendar_category_id' => ['required'],
            'name' => ['required'],
            'description' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );

        // si fallan las validaciones normales tirar excepción
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }else{
            if($request->start_date && $request->end_date){
                $start_datetime = explode('T', $request->start_date);
                $start_datetime = $start_datetime[0] . ' ' . $request->start_time;

                $end_datetime = explode('T', $request->end_date);
                $end_datetime = $end_datetime[0] . ' ' . $request->end_time;

                $start_date = Carbon::parse($start_datetime);
                $end_date = Carbon::parse($end_datetime);

                $range_date = CarbonPeriod::create($start_date,  $end_date);
                if(!$range_date->valid()){
                    throw new HttpResponseException(
                        response()->json(['errors' => __('messages.validate-date-range')], 400)
                    );
                }
            }
        }
    }
}
