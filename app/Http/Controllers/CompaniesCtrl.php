<?php

namespace App\Http\Controllers;


use App\Models\Address;
use App\Models\CompanyCategory;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Company;

class CompaniesCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function all(Request $request){
        $order = ($request->activeSort) ? $request->activeSort : 'name';
        $direction = ($request->sortDirection) ? $request->sortDirection : 'asc';

        $query = Company::orderBy($order, $direction);

        if($request->trash){
            $query->onlyTrashed();
        }else if($request->withTrashed){
            $query->withTrashed();
        }

        if($request->filter){
            $query->where(function($q) use ($request){
                $q->where('name', 'like', '%'.$request->filter.'%');
                $q->orWhere('email', 'like', '%'.$request->filter.'%');
                $q->orWhere('phone', 'like', '%'.$request->filter.'%');
            });
        }

        if($request->perPage == -1){
            return $query->get();
        }else{
            return $query->paginate($request->perPage);
        }
    }

    public function get(Company $company){
        return $company->_data();
    }

    public function store(Request $request){
        return $this->saveData(false, $request)->_data();
    }

    public function update(Company $company, Request $request){
        return $this->saveData($company, $request)->_data();
    }

    public function delete($id, Request $request){
        $company = Company::withTrashed()->find($id);
        if($company){
            if($request->forceDelete){
                $denials = $company->_deleteAllowed();
                if(!count($denials)) {
                    $company->forceDelete();
                    $company->_afterDelete();
                }else{
                    throw new HttpResponseException(
                        response()->json(['errors' => $denials], 400)
                    );
                }
            }else{
                $company->delete();
            }
            return $company->_data();
        }
        return array();
    }

    public function restore($id){
        $company = Company::withTrashed()->where('id', $id)->first();
        if($company){
            $company->restore();
            return $company->_data();
        }
        return array();
    }

    public function categories(){
        return CompanyCategory::orderBy('id', 'asc')->get();
    }



    private function saveData($company, $request){
        if(!$company){
            $company = new Company;
        }
        $this->validateData($request);

        // save company data
        $company->fill($request->all());
        $company->save();

        Address::_save_polymorphic( $company, 'company_address_id', $request->address );

        return $company;
    }


    private function validateData($request){
        $rules = [
            'company_category_id' => ['required'],
            'name' => ['required'],
            'email' => ['nullable', 'email'],
        ];

        $validator = Validator::make( $request->all(), $rules );
        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors'=>$validator->errors()], 400)
            );
        }
    }
}
