<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InstansiController extends Controller
{
    public function index(){
        $title = "Instansi";
        $nav = 'instansi';
        return view('backends.instansi', compact('title', 'nav'));
    }

    public function add(){
        $title = 'Tambah Instansi';
        $nav = 'instansi';
        return view('backends.tambahinstansi', compact('title', 'nav'));
    }

    public function edit($id){
        $title = 'Edit Instansi';
        $nav = 'instansi';
        $id = $id;
        return view('backends.editinstansi', compact('title', 'nav', 'id'));
    }
}
