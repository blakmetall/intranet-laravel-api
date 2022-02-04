<?php

namespace App\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\MahgazineSection;

class MahgazineSectionsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'order';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = MahgazineSection::orderBy($order, $direction);
        $query->with('edition');

        if($request->trash){
            $query->onlyTrashed();
        }else if($request->withTrashed){
            $query->withTrashed();
        }

        if($request->filter){
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->filter . '%');
                $q->orWhere('created_at', 'LIKE','%'.$request->filter.'%');
            });
        }

        if($request->filterByEdition){
            $query->where('mahgazine_edition_id', $request->filterByEdition);
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(MahgazineSection $section){
        return $section->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(MahgazineSection $section, Request $request){
        return $this->saveData($section, $request)->_data();
    }

    public function delete($id, Request $request){
        $section = MahgazineSection::withTrashed()->find($id);
        if($section){
            if($request->forceDelete){
                $denials = $section->_deleteAllowed();
                if(!count($denials)) {
                    $section->forceDelete();
                    $section->_afterDelete();
                }else{
                    throw new HttpResponseException(
                        response()->json(['errors' => $denials], 400)
                    );
                }
            }else{
                $section->delete();
            }
            return $section->_data();
        }
        return array();
    }

    public function restore($id){
        $section = MahgazineSection::withTrashed()->where('id', $id)->first();
        if($section){
            $section->restore();
            return $section->_data();
        }
        return array();
    }

    public function order(MahgazineSection $section, $direction){
        if( ! ($direction == 1 || $direction == -1) ){ exit; } // filter direction to 1 or -1

        $edition = $section->edition;
        $edition_sections = $edition->sections()->orderBy('order', 'asc')->get();

        $update_sections = [];
        $order_numbers = [];

        // prepare ordering numbers
        if(count($edition_sections)){
            foreach($edition_sections as $k => $_section){
                if(!in_array($_section->order, $order_numbers)){
                    $order_number = $_section->order;
                }else{
                    $order_number = $edition_sections[$k - 1]->order + 1;
                }

                $order_numbers[] = $order_number;
                $update_sections[] = [
                    'id' => $_section->id,
                    'order' => $order_number,
                ];
            }
        }

        // find and replace according to direction
        if(
            ($direction == -1 && !($section->id == $update_sections[0]['id'])) ||
            ($direction == 1 && !($section->id == $update_sections[count($update_sections)-1]['id']))
        ){
            foreach($update_sections as $k => $section_data){
                if($section->id == $section_data['id']){
                    $section_data_number = $section_data['order'];

                    $update_sections[$k]['order'] = $update_sections[$k + $direction]['order'];
                    $update_sections[$k + $direction]['order'] = $section_data_number;
                }
            }

            // updates new order to database
            foreach($update_sections as $section_data){
                $order = MahgazineSection::find($section_data['id']);
                if($order){
                    $order->order = $section_data['order'];
                    $order->save();
                }
            }
        }
    }

    private function saveData($section, $request){
        if(!$section){
            $section = new MahgazineSection;

            $last_insert_section = MahgazineSection::where('mahgazine_edition_id', $request->mahgazine_edition_id)
                ->orderBy('order', 'desc')
                ->first();

            $order_number = ($last_insert_section) ? $last_insert_section->order + 1 : 1;
            $section->order = $order_number;
        }

        $this->validateData($request);
        $section->fill($request->all());
        $section->save();

        return $section;
    }


    private function validateData($request){
        $rules = [
            'name' => ['required'],
            'color' => ['required'],
            'template_slug' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }
    }
}
