<?php

namespace App\Http\Controllers;

class PbphController extends Controller
{
    public function index(){
        $title = "PBPH - Jagakampung";
        $nav = 'pbph';
        return view('backends.pbph', compact('nav', 'title'));
    }

    public function add(){
        $title = "Tambah Info PBPH - Jagakampung";
        $nav = 'pbph';
        return view('backends.tambah-pbph', compact('nav', 'title'));
    }

    public function edit($id){
        $title = "Edit Info PBPH - Jagakampung";
        $nav = 'pbph';
        return view('backends.edit-pbph', compact('nav', 'title', 'id'));
    }
}
