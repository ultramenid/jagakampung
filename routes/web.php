<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GrupController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\InstansiController;
use App\Http\Controllers\KonflikController;
use App\Http\Controllers\LocalServiceController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\checkSession;
use App\Http\Middleware\hasSession;
use App\Http\Middleware\setLanguage;
use Illuminate\Support\Facades\Route;


Route::redirect('/', 'en');


Route::middleware([setLanguage::class])->group(function () {
    Route::group(['prefix' => '{lang}'], function () {
        Route::get('/', [IndexController::class, 'index'])->name('index');

    });
});

    Route::get('/cms/rest-map', [LocalServiceController::class, 'index']);
    Route::get('/cms/rest-map/{id}', [LocalServiceController::class, 'kasusDetail']);

//redirect to login page if user has no session
Route::middleware([checkSession::class])->group(function () {
    Route::get('/cms/tambah-konflik', [KonflikController::class, 'add']);
    Route::get('/cms/tambah-group', [GrupController::class, 'add']);
    Route::get('/cms/tambah-perusahaan', [PerusahaanController::class, 'add']);
    Route::get('/cms/tambah-user', [UsersController::class, 'add']);
    Route::get('/cms/tambah-instansi', [InstansiController::class, 'add']);

    Route::get('/cms/editgroup/{id}', [GrupController::class, 'edit']);
    Route::get('/cms/editperusahaan/{id}', [PerusahaanController::class, 'edit']);
    Route::get('/cms/edituser/{id}', [UsersController::class, 'edit']);
    Route::get('/cms/editinstansi/{id}', [InstansiController::class, 'edit']);
    Route::get('/cms/edit-konflik/{id}', [KonflikController::class, 'edit']);

    Route::get('/cms/dashboard', [DashboardController::class, 'index']);
    Route::get('/cms/group', [GrupController::class, 'index']);
    Route::get('/cms/perusahaan', [PerusahaanController::class, 'index']);
    Route::get('/cms/konflik', [KonflikController::class, 'index']);
    Route::get('/cms/users', [UsersController::class, 'index']);
    Route::get('/cms/instansi', [InstansiController::class, 'index']);





    Route::group(['prefix' => '/cms/fire-filemanager'], function () {
        \UniSharp\LaravelFilemanager\Lfm::routes();
    });

});

//redirect to dashboard if user has session to dashboard
Route::middleware([hasSession::class])->group(function () {
    Route::get('/cms/login', [DashboardController::class, 'login']);
});

//url to logout session
Route::get('/cms/logout', function () {
    session()->flush();
    return redirect('/cms/login');
});

