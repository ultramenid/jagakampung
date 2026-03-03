<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KonflikController extends Controller
{
    public function add(){
        $title = 'Tambah Konflik';
        $nav = 'konflik';
        return view('backends.tambahkonflik', compact('title', 'nav'));
    }

    public function index(){
        $title = "Konflik - Jagakampung";
        $nav = 'konflik';
        return view('backends.konflik', compact('nav', 'title'));
    }

    public function edit($id){
        $title = 'Edit Konflik';
        $nav = 'konflik';
        $id = $id;
        return view('backends.editkonflik', compact('title', 'nav', 'id'));
    }
}
