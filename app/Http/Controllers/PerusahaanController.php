<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PerusahaanController extends Controller
{
    public function index(){
        $title = "Perusahaan - Jagakampung";
        $nav = 'perusahaan';
        return view('backends.perusahaan', compact('nav', 'title'));
    }


    public function add(){
        $title = "Tambah Perusahaan - Jagakampung";
        $nav = 'perusahaan';
        return view('backends.tambah-perusahaan', compact('nav', 'title'));
    }

    public function edit($id){
        $title = "Edit Perusahaan - Jagakampung";
        $nav = 'perusahaan';
        return view('backends.edit-perusahaan', compact('nav', 'title', 'id'));
    }
}
