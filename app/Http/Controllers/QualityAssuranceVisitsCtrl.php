<?php

namespace App\Http\Controllers;


use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\QualityAssuranceVisit;
use Carbon\Carbon;

class QualityAssuranceVisitsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }
    
    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'created_at';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'desc';

        $profileHotel = Auth::user()->profile->hotel;
        $hotel_id = ($profileHotel) ? $profileHotel->id : -1;

        if($order == 'hotel') {
            // order by field in related table
            $query = QualityAssuranceVisit::join('hotels as h', 'h.id', '=', 'quality_assurance_visits.hotel_id')
                ->orderBy('h.name', $direction)
                ->with('hotel')
                ->with('status')
                ->with('userOwner.profile')
                ->select('quality_assurance_visits.*');

            if($request->filter){
                $query->where(function($q) use ($request) {
                    $q->where('h.name', 'LIKE', '%'.$request->filter.'%');
                    $q->orWhere('quality_assurance_visits.id', $request->filter);
                    $q->orWhere('quality_assurance_visits.datetime', 'LIKE', '%'.$request->filter.'%');
                    $q->orWhere('quality_assurance_visits.created_at', 'LIKE', '%'.$request->filter.'%');
                });
            }

            // get assurance visits only if user works for that hotel
            if($request->filterByUserProfileHotel){
                $query->where('quality_assurance_visits.hotel_id', $hotel_id);
            }

        }else {
            $query = QualityAssuranceVisit::orderBy($order, $direction);
            $query->with('hotel');
            $query->with('status');
            $query->with('userOwner.profile');

            if ($request->filter) {
                $query->where(function($q) use ($request){
                    $q->whereHas('hotel', function ($q2) use ($request) {
                        $q2->where('name', 'LIKE', '%' . $request->filter . '%');
                    });

                    $q->orWhereHas('userOwner', function ($q2) use ($request) {
                        $q2->whereHas('profile', function($q3) use ($request) {
                            $q3->where('full_name', 'LIKE', '%' . $request->filter . '%');
                        });
                    });

                    $q->orWhere('id', $request->filter);
                    $q->orWhere('datetime', 'LIKE','%'.$request->filter.'%');
                    $q->orWhere('created_at', 'LIKE','%'.$request->filter.'%');
                });
            }

            // get assurance visits only if user works for that hotel
            if($request->filterByUserProfileHotel){
                $query->where('hotel_id', $hotel_id);
            }
        }


        if ($request->perPage == -1) {
            return $query->get();
        } else {
            return $query->paginate($request->perPage);
        }
    }

    public function get(QualityAssuranceVisit $visit){
        return $visit->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(QualityAssuranceVisit $visit, Request $request){
        return $this->saveData($visit, $request)->_data();
    }

    public function delete($id)
    {
        $visit = QualityAssuranceVisit::find($id);
        if ($visit) {
            $denials = $visit->_deleteAllowed();
            if (count($denials)) {
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            } else {
                $visit->delete();
                $visit->_afterDelete();
            }
            return $visit->_data();
        }
    }

    private function saveData($visit, $request){
        $notify = false;
        if (!$visit) {
            $visit = new QualityAssuranceVisit;
            $visit->owner_user_id = Auth::id();
            $notify = true;
        }
        $this->validateData($request);


        $profile = Auth::user()->profile;
        $format = 'Y-m-d H:i:s';
        $timezone = ($profile->use_local_timezone) ? $request->header('APP_TIMEZONE') : $profile->timezone;

        // start date
        $date = Carbon::parse($request->date, 'UTC');
        $date_with_time_string = $date->format('Y-m-d') . ' ' . $request->time . ':00';
        $datetime = Carbon::createFromFormat($format, $date_with_time_string, $timezone);

        $visit->fill($request->all());
        $visit->datetime = $datetime->setTimezone('UTC')->format($format);
        $visit->save();

        if($notify){
            $users = User::whereHas('profile', function($q) use ($visit){
                $q->where('hotel_id', $visit->hotel_id);
            })->get();

            if($users){
                foreach($users as $user){
                    $notification = New Notification;
                    $notification->user_id = $user->id;
                    $notification->type = 'event';
                    $notification->title = 'N_ASSURANCE_VISIT';
                    $notification->url = '/quality/assurance-visits/view/' . $visit->id;
                    $notification->save();
                }
            }
        }

        return $visit;
    }

    private function validateData($request){
        $rules = [
            'hotel_id' => 'required',
            'revision_number' => 'nullable|numeric',
            'score' => 'nullable|numeric',
        ];

        //$validator = Validator::make($request->all(), $rules, $messages);
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors' => $validator->errors()], 400)
            );
        }
    }
}
