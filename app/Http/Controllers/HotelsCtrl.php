<?php

namespace App\Http\Controllers;


use App\Models\Address;
use App\Models\HotelBrandsiteSection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Hotel;

class HotelsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'order';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = Hotel::orderBy($order, $direction);

        if($request->trash){
            $query->onlyTrashed();
        }else if($request->withTrashed){
            $query->withTrashed();
        }

        if($request->filter){
            $query->where(function($q) use ($request){
                $q->where('name', 'like', '%'.$request->filter.'%');
                $q->orWhere('email', 'like', '%'.$request->filter.'%');
                $q->orWhere('website', 'like', '%'.$request->filter.'%');
            });
        }

        if($request->isEnabled){
            $query->where('is_enabled', 1);
        }

        if($request->withBrandsiteSections){
            $query->with('brandsiteSections');
        }

        if($request->withLogo){
           $query->with('logo');
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(Hotel $hotel){
        return $hotel->_data();
    }

    public function getBrandsiteSections(Hotel $hotel){
        $hotel->_initializeBrandsiteSections(); // create brandsite sections for this hotel if not already there

        // brandsite sections created by this hotel
        return $hotel->hotelBrandsiteSections()->with('brandsiteSection')->get();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(Hotel $hotel, Request $request){
        return $this->saveData($hotel, $request)->_data();
    }

    public function updateBrandsiteSections(Hotel $hotel, Request $request){

        if($request->hotelBrandsiteSections && count($request->hotelBrandsiteSections)){
            foreach($request->hotelBrandsiteSections as $section){

                if($hotel->id == $section['hotel_id']){
                    $hotelBrandsiteSection = HotelBrandsiteSection::find($section['id']);
                    if($hotelBrandsiteSection){
                        $hotelBrandsiteSection->is_enabled = $section['is_enabled'];
                        $hotelBrandsiteSection->save();
                    }
                }
            }
        }

    }

    public function delete($id, Request $request){
        $hotel = Hotel::withTrashed()->find($id);
        if($hotel){
            if($request->forceDelete){
                $denials = $hotel->_deleteAllowed();
                if(!count($denials)) {
                    $hotel->forceDelete();
                    $hotel->_afterDelete();
                }else{
                    throw new HttpResponseException(
                        response()->json(['errors' => $denials], 400)
                    );
                }
            }else{
                $hotel->delete();
            }
            return $hotel->_data();
        }
        return array();
    }

    public function restore($id){
        $hotel = Hotel::withTrashed()->where('id', $id)->first();
        if($hotel){
            $hotel->restore();
            return $hotel->_data();
        }
        return array();
    }

    public function order(Hotel $hotel, $direction){
        if( ! ($direction == 1 || $direction == -1) ){ exit; } // filter direction to 1 or -1

        $hotels = Hotel::orderBy('order', 'asc')->get();

        $update_hotels = [];
        $order_numbers = [];

        // prepare ordering numbers
        if(count($hotels)){
            foreach($hotels as $k => $_hotel){
                if(!in_array($_hotel->order, $order_numbers)){
                    $order_number = $_hotel->order;
                }else{
                    $order_number = $hotels[$k - 1]->order + 1;
                }

                $order_numbers[] = $order_number;
                $update_hotels[] = [
                    'id' => $_hotel->id,
                    'order' => $order_number,
                ];
            }
        }

        // find and replace according to direction
        if(
            ($direction == -1 && !($hotel->id == $update_hotels[0]['id'])) ||
            ($direction == 1 && !($hotel->id == $update_hotels[count($update_hotels)-1]['id']))
        ){
            foreach($update_hotels as $k => $hotel_data){
                if($hotel->id == $hotel_data['id']){
                    $hotel_data_number = $hotel_data['order'];

                    $update_hotels[$k]['order'] = $update_hotels[$k + $direction]['order'];
                    $update_hotels[$k + $direction]['order'] = $hotel_data_number;
                }
            }

            // updates new order to database
            foreach($update_hotels as $hotel_data){
                $order = Hotel::find($hotel_data['id']);
                if($order){
                    $order->order = $hotel_data['order'];
                    $order->save();
                }
            }
        }
    }

    private function saveData($hotel, $request){
        if(!$hotel){
            $hotel = new Hotel;

            $last_hotel = Hotel::orderBy('order', 'desc')->first();
            $order_number = ($last_hotel) ? $last_hotel->order + 1 : 1;
            $hotel->order = $order_number;
        }
        $this->validateData($request);

        $hotel->fill($request->all());
        $hotel->save();

        Address::_save_polymorphic( $hotel, 'hotel_address_id', $request->address );

        return $hotel;
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
        }
    }
}
