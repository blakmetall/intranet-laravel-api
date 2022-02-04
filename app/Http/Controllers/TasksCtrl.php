<?php

namespace App\Http\Controllers;


use App\Models\Notification;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskStatus;
use Auth;

class TasksCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'name';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = Task::orderBy($order, $direction);


        $query->with('status'); // load status data on list
        $query->with('userOwner.profile');
        $query->with('userAssigned.profile');

        if($request->filter){
            $query->where(function($q) use ($request){
                $q->where('name', 'like', '%'.$request->filter.'%');
                $q->orWhere('description', 'like', '%'.$request->filter.'%');
            });
        }

        if($request->filterByLoggedUser && Auth::id()){
            $query->where(function($q){
                 $q->where('owner_user_id', Auth::id());
                 $q->orWhere('assigned_user_id', Auth::id());
            });
        }

        if($request->filterArchive){
            $query->whereHas('status', function($q){
                $q->where('slug', 'archived');
            });
        }

        if($request->isPinnedToCalendar){
            $query->where('is_pinned_to_calendar', 1);
        }

        if($request->start_date && $request->end_date){
            $query->where(function($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date]);
                $q->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
            });
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(Task $task){
        return $task->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(Task $task, Request $request){
        return $this->saveData($task, $request)->_data();
    }

    public function delete($id){
        $task = Task::find($id);
        if ($task) {
            $denials = $task->_deleteAllowed();
            if (count($denials)) {
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            } else {
                $task->delete();
                $task->_afterDelete();
            }
            return $task->_data();
        }
    }

    public function archive(Task $task){
        $status = TaskStatus::where('slug', 'archived')->first();
        if($status){
            $task->task_status_id = $status->id;
            $task->save();
        }

        return $task;
    }

    public function statusList(){
        return TaskStatus::orderBy('id', 'asc')->get();
    }

     public function changeStatus(Task $task, Request $request){
        $status = TaskStatus::where('slug', $request->status)->first();
        if($status){
            $task->task_status_id = $status->id;
            $task->save();

            $notification = New Notification;
            $notification->user_id = $task->assigned_user_id;
            $notification->type = 'task';
            $notification->url = '/tasks/form/' . $task->id;
            $notification->title = 'N_FINISHED_TASK';
            $notification->save();
        }

        return $task->_data();
    }

    private function saveData($task, $request){
        $notify = false;
        $notify_updated = false;
        $notify_finished = false;

        if(!$task){
            $task = new Task;
            $task->owner_user_id = Auth::id();
            $notify = true;
        }else{
            if($request->assigned_user_id != $task->assigned_user_id){
                $notify = true;
            }
        }

        $this->validateData($request);

        // notify if finished
        if($task->task_status_id != $request->task_status_id && $request->task_status_id == 4){
            $notify = true;
            $notify_finished = true;
        }

        $task->fill($request->all());

        // start date
        $start_date = Carbon::parse($request->start_date);
        $task->start_date = $start_date->format('Y-m-d');

        // end date
        if($request->end_date){
            $end_date = Carbon::parse($request->end_date);
            $task->end_date = $end_date->format('Y-m-d');
        }else{
            $task->end_date = null;
        }

        $task->save();


        if($task->created_at != $task->updated_at) {
            $notify = true;
            $notify_updated = true;
        }



        if($notify){
            $notification = New Notification;
            $notification->user_id = $task->assigned_user_id;
            $notification->type = 'task';
            $notification->url = '/tasks/view/' . $task->id;

            // might be override
            $notification->title = 'N_NEW_TASK';

            if($notify_updated) {
                $notification->title = 'N_UPDATED_TASK';
            }

            if($notify_finished) {
                $notification->title = 'N_FINISHED_TASK';
            }

            $notification->save();
        }

        return $task;
    }

    private function validateData($request){

        $rules = [
            'name' => ['required'],
            'description' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }else{

            // validate extra range dates if both are sent
            if($request->start_date && $request->end_date){
                $start_date = Carbon::parse($request->start_date);
                $end_date = Carbon::parse($request->end_date);

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
