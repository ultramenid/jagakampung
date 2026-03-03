<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GrupController extends Controller
{

    public function index(){
        $title = "Grup - Jagakampung";
        $nav = 'grup';
        return view('backends.grup', compact('nav', 'title'));
    }

    public function add(){
        $title = "Tambah Grup - Jagakampung";
        $nav = 'grup';
        return view('backends.tambah-grup', compact('nav', 'title'));
    }

    public function edit($id){
        $title = "Edit Grup - Jagakampung";
        $nav = 'grup';
        $id = $id;
        return view('backends.edit-grup', compact('nav', 'title', 'id'));
    }
}
