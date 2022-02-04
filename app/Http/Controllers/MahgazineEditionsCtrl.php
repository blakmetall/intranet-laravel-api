<?php

namespace App\Http\Controllers;

use Carbon\CarbonPeriod;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\MahgazineEdition;
use Auth;
use Carbon\Carbon;

class MahgazineEditionsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'end_datetime';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'desc';

        $query = MahgazineEdition::orderBy($order, $direction);

        if($request->trash){
            $query->onlyTrashed();
        }else if($request->withTrashed){
            $query->withTrashed();
        }

        if($request->filter){
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->filter . '%');
                $q->orWhere('id', $request->filter);
                $q->orWhere('start_datetime', 'LIKE','%'.$request->filter.'%');
                $q->orWhere('end_datetime', 'LIKE','%'.$request->filter.'%');
                $q->orWhere('created_at', 'LIKE','%'.$request->filter.'%');
            });
        }

        if($request->isPublished){
            $query->where('is_published', 1);
        }
        
        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(MahgazineEdition $edition){
        return $edition->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(MahgazineEdition $edition, Request $request){
        return $this->saveData($edition, $request)->_data();
    }

    public function delete($id, Request $request){
        $edition = MahgazineEdition::withTrashed()->find($id);
        if($edition){
            if($request->forceDelete){
                $denials = $edition->_deleteAllowed();
                if(!count($denials)) {
                    $edition->forceDelete();
                    $edition->_afterDelete();
                }else{
                    throw new HttpResponseException(
                        response()->json(['errors' => $denials], 400)
                    );
                }
            }else{
                $edition->delete();
            }
            return $edition->_data();
        }
        return array();
    }

    public function restore($id){
        $edition = MahgazineEdition::withTrashed()->where('id', $id)->first();
        if($edition){
            $edition->restore();
            return $edition->_data();
        }
        return array();
    }

    private function saveData($edition, $request){
        if(!$edition){
            $edition = new MahgazineEdition;
        }
        $this->validateData($request);

        $edition->fill($request->all());

        $profile = Auth::user()->profile;
        $format = 'Y-m-d H:i:s';
        $timezone = ($profile->use_local_timezone) ? $request->header('APP_TIMEZONE') : $profile->timezone;

        // start date
        $start_date = Carbon::parse($request->start_date, 'UTC');
        $start_date_with_time_string = $start_date->format('Y-m-d') . ' ' . $request->start_time . ':00';
        $start_datetime = Carbon::createFromFormat($format, $start_date_with_time_string, $timezone);
        $edition->start_datetime = $start_datetime->setTimezone('UTC')->format($format);

        // end date
        $end_date = Carbon::parse($request->end_date, 'UTC');
        $end_date_with_time_string = $end_date->format('Y-m-d') . ' ' . $request->end_time . ':00';
        $end_datetime = Carbon::createFromFormat($format, $end_date_with_time_string, $timezone);
        $edition->end_datetime = $end_datetime->setTimezone('UTC')->format($format);
        
        $edition->save();
        
        // unpublish the rest of the articles
        if($request->is_published){
            MahgazineEdition
                ::where('is_published', 1)
                ->where('id', '!=', $edition->id)
                ->update(['is_published' => 0]);
        }

        return $edition;
    }


    private function validateData($request){
        $rules = [
            'name' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );
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
