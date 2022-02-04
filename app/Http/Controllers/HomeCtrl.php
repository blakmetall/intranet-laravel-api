<?php

namespace App\Http\Controllers;

class HomeCtrl extends Controller
{
    public function index(){
        return view('home');
    }
}
