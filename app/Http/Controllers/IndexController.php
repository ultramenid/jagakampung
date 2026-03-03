<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(){
        $title = 'Jaga Kampung';
        return view('frontends.index', compact('title'));
    }
}
