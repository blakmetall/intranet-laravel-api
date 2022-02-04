<?php

namespace App\Http\Controllers;

use App\Models\HotelBrandsiteSection;
use Illuminate\Http\Request;

class HotelsBrandsiteSectionsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function get(HotelBrandsiteSection $hotelBrandsiteSection){
        return $hotelBrandsiteSection->_data();
    }
}
