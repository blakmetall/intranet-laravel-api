<?php

namespace App\Http\Controllers;


use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\QualityVisitExensionRequest;
use Auth;

class QualityVisitsExensionsRequestsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }
    
    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'created_at';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'desc';

        $query = QualityVisitExensionRequest::with('hotel');
        $query->with('userOwner.profile');

        if($request->filter){
            $query->where('policy', 'LIKE', '%'.$request->filter.'%');
            $query->orWhere('application_reasoning', 'LIKE', '%'.$request->filter.'%');
            $query->orWhere('guests_collateral_damage', 'LIKE', '%'.$request->filter.'%');
            $query->orWhereHas('hotel', function ($q) use ($request) {
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

    public function get(QualityVisitExensionRequest $exensionRequest){
        return $exensionRequest->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(QualityVisitExensionRequest $exensionRequest, Request $request){
        return $this->saveData($exensionRequest, $request)->_data();
    }

    public function delete($id){
        $exensionRequest = QualityVisitExensionRequest::find($id);
        if ($exensionRequest) {
            $denials = $exensionRequest->_deleteAllowed();
            if (count($denials)) {
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            } else {
                $exensionRequest->delete();
                $exensionRequest->_afterDelete();
            }
            return $exensionRequest->_data();
        }
    }

    public function approve(QualityVisitExensionRequest $exensionRequest){
        $exensionRequest->_approve();

        $notification = New Notification;
        $notification->user_id = $exensionRequest->owner_user_id;
        $notification->type = 'quality';
        $notification->title = 'N_EXENSION_REQUEST_APPROVED';
        $notification->url = '/quality/exension-requests/view/' . $exensionRequest->id;
        $notification->save();

        return $exensionRequest->_data();
    }

    public function deny(QualityVisitExensionRequest $exensionRequest){
        $exensionRequest->_deny();

        $notification = New Notification;
        $notification->user_id = $exensionRequest->owner_user_id;
        $notification->type = 'quality';
        $notification->title = 'N_EXENSION_REQUEST_DENIED';
        $notification->url = '/quality/exension-requests/view/' . $exensionRequest->id;
        $notification->save();

        return $exensionRequest->_data();
    }


    private function saveData($exensionRequest, $request){
        $notify = false;
        if(!$exensionRequest){
            $exensionRequest = new QualityVisitExensionRequest;
            $exensionRequest->owner_user_id = Auth::id();

            $notify = true;
        }
        $this->validateData($request);

        $exensionRequest->fill($request->all());
        $exensionRequest->save();

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
                    $notification->title = 'N_EXENSION_REQUEST';
                    $notification->url = '/admin/quality/exension-requests/view/' . $exensionRequest->id;
                    $notification->save();
                }
            }
        }

        return $exensionRequest;
    }


    private function validateData($request){
        $rules = [
            'hotel_id' => ['required'],
            'policy' => ['required'],
            'application_reasoning' => ['required'],
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
