<?php

namespace App\Http\Controllers;


use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\QualityVisitExtensionRequest;
use Carbon\Carbon;

class QualityVisitsExtensionsRequestsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'created_at';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'desc';

        $query = QualityVisitExtensionRequest::with('assuranceVisit.hotel');
        $query->with('userOwner.profile');

        if($request->filter){
            $query->where('policy', 'LIKE', '%'.$request->filter.'%');
            $query->orWhere('application_reasoning', 'LIKE', '%'.$request->filter.'%');
            $query->orWhere('guests_collateral_damage', 'LIKE', '%'.$request->filter.'%');
            $query->orWhere('extension_date', 'LIKE', '%'.$request->filter.'%');
            $query->orWhereHas('assuranceVisit.hotel', function ($q) use ($request) {
                $q->where('name','LIKE', '%'.$request->filter.'%');
            });

            $query->orWhere('id', $request->filter);
            $query->orWhere('created_at', 'LIKE','%'.$request->filter.'%');
        }

        $query->orderBy($order, $direction);

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(QualityVisitExtensionRequest $extensionRequest){
        return $extensionRequest->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(QualityVisitExtensionRequest $extensionRequest, Request $request){
        return $this->saveData($extensionRequest, $request)->_data();
    }

    public function delete($id){
        $extensionRequest = QualityVisitExtensionRequest::find($id);
        if ($extensionRequest) {
            $denials = $extensionRequest->_deleteAllowed();
            if (count($denials)) {
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            } else {
                $extensionRequest->delete();
                $extensionRequest->_afterDelete();
            }
            return $extensionRequest->_data();
        }
    }

    public function approve(QualityVisitExtensionRequest $extensionRequest){
        $extensionRequest->_approve();

        $assurance_visit = $extensionRequest->assuranceVisit;
        $assurance_visit->datetime = $extensionRequest->extension_date;
        $assurance_visit->save();


        $notification = New Notification;
        $notification->user_id = $extensionRequest->owner_user_id;
        $notification->type = 'quality';
        $notification->title = 'N_EXTENSION_REQUEST_APPROVED';
        $notification->url = '/quality/extension-requests/view/' . $extensionRequest->id;
        $notification->save();

        return $extensionRequest;
    }

    public function deny(QualityVisitExtensionRequest $extensionRequest){
        $extensionRequest->_deny();

        $notification = New Notification;
        $notification->user_id = $extensionRequest->owner_user_id;
        $notification->type = 'quality';
        $notification->title = 'N_EXTENSION_REQUEST_DENIED';
        $notification->url = '/quality/extension-requests/view/' . $extensionRequest->id;
        $notification->save();

        return $extensionRequest;
    }

    private function saveData($extensionRequest, $request){
        $notify = false;
        if(!$extensionRequest){
            $extensionRequest = new QualityVisitExtensionRequest;
            $extensionRequest->owner_user_id = Auth::id();

            $notify = true;
        }

        $this->validateData($request);
        $extensionRequest->fill($request->all());

        $profile = Auth::user()->profile;
        $format = 'Y-m-d H:i:s';
        $timezone = ($profile->use_local_timezone) ? $request->header('APP_TIMEZONE') : $profile->timezone;

        // start date
        $date = Carbon::parse($request->date, 'UTC');
        $date_with_time_string = $date->format('Y-m-d') . ' ' . $request->time . ':00';
        $datetime = Carbon::createFromFormat($format, $date_with_time_string, $timezone);
        $extensionRequest->extension_date = $datetime->setTimezone('UTC')->format($format);


        $extensionRequest->save();


        if($notify){
            $users = User
                ::where('user_role_id', 3)    // corporative
                ->orWhere('user_role_id', 2)    // admin
                ->orWhere('user_role_id', 1)      // super admin
                ->get();

            if($users){
                foreach($users as $user){
                    $notification = New Notification;
                    $notification->user_id = $user->id;
                    $notification->type = 'quality';
                    $notification->title = 'N_EXTENSION_REQUEST';
                    $notification->url = '/admin/quality/extension-requests/view/' . $extensionRequest->id;
                    $notification->save();
                }
            }
        }

        return $extensionRequest;
    }


    private function validateData($request){
        $rules = [
            'application_reasoning' => ['required'],
            'policy' => ['required'],
            'guests_collateral_damage' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }
    }
}
