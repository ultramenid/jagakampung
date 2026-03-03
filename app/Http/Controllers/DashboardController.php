<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function login(){
        $title = 'Login - Jagakampung';
        return view('backends.login', compact('title'));
    }

    public function index(){
        $title = 'Dashboard - Jagakampung';
        $nav = 'dashboard';
        return view('backends.dashboard', compact('title', 'nav'));
    }
}
