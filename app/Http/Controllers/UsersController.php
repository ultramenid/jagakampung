<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index(){
        $title = "Users - Jagakampung";
        $nav = 'users';
        return view('backends.users', compact('nav', 'title'));
    }

    public function add(){
        $title = "Tambah Users - Jagakampung";
        $nav = 'users';
        return view('backends.tambah-user', compact('nav', 'title'));
    }

    public function edit($id){
        $title = "Edit Users - Jagakampung";
        $nav = 'users';
        return view('backends.edit-user', compact('nav', 'title', 'id'));
    }
}
