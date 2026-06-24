<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function index()
    {
        return view('cms.artikel.index');
    }

    public function add($id) 
    {
        $title = 'Tambah Artikel';
        $nav = 'artikel';
        $id = $id;
        return view('backends.tambah-artikel', compact('title', 'nav', 'id'));
    }

    public function edit($id)
    {
        $title = 'Edit Artikel';
        $nav = 'artikel';
        $id = $id;
        return view('backends.edit-artikel', compact('title', 'nav', 'id'));
    }
}
