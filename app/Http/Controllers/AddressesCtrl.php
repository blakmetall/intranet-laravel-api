<?php

namespace App\Http\Controllers;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Address;

class AddressesCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'street';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = Address::orderBy($order, $direction);

        if($request->filter){
            $query->where('street', 'like', '%'.$request->filter.'%');
            $query->orWhere('colony', 'like', '%'.$request->filter.'%');
            $query->orWhere('interior_number', $request->filter);
            $query->orWhere('exterior_number', $request->filter);
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(Address $address){
        return $address->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(Address $address, Request $request){
        return $this->saveData($address, $request)->_data();
    }

    public function delete($id){
        $address = Address::find($id);
        if($address){
            $denials = $address->_deleteAllowed();
            if(count($denials)){
                throw new HttpResponseException(
                    response()->json(['errors' => $denials], 400)
                );
            }else {
                $address->delete();
                $address->_afterDelete();
            }

            return $address->_data();
        }
        return array();
    }

    private function saveData($address, $request){
        if(!$address){
            $address = new Address;
        }
        $this->validateData($request);

        $address->fill($request->all());
        $address->save();

        return $address;
    }


    private function validateData($request){
        $rules = [
            'country_id' => ['required'],
            'state_id' => ['required'],
            'street' => ['required'],
            'exterior_number' => ['required'],
            'colony' => ['required'],
            'municipality_county' => ['required'],
            'zip' => ['required'],
        ];

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }
    }
}
