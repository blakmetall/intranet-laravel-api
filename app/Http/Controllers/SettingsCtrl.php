<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsCtrl extends Controller
{
    public function __construct(Request $request){
        \App::setLocale($request->header('APP_LANG'));
    }

    public function get(){
        $setting = Setting::where('id', 1)->first();
        return $setting;
    }

    public function update(Request $request){
        $setting = Setting::where('id', 1)->first();
        $setting->fill($request->all());
        $setting->save();

        return $setting;
    }
}
