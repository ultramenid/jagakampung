<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ArtikelController,
    DashboardController,
    GrupController,
    IndexController,
    InstansiController,
    KonflikController,
    LocalServiceController,
    PerusahaanController,
    UsersController,
};
use App\Http\Middleware\{
    adminSession,
    blockPathTraversal,
    checkSession,
    hasSession,
    setLanguage,
};

// ponytail: locale prefix (/en, /id) disabled for now — serve index directly.
// Re-enable by restoring the redirect + setLanguage group below.
Route::get("/", [IndexController::class, "index"])->name("index");

// Route::redirect("/", "id");
// Route::middleware([setLanguage::class])->group(function () {
//     Route::group(["prefix" => "{lang}"], function () {
//         Route::get("/", [IndexController::class, "index"])->name("index");
//     });
// });

Route::get("/cms/rest-map", [LocalServiceController::class, "index"]);
Route::get("/cms/rest-map/{id}", [
    LocalServiceController::class,
    "kasusDetail",
]);

//redirect to login page if user has no session
Route::middleware([checkSession::class])->group(function () {
    // Any authenticated user: dashboard + their own konflik/artikel
    Route::get("/cms/tambah-konflik", [KonflikController::class, "add"]);
    Route::get("/cms/edit-konflik/{id}", [KonflikController::class, "edit"]);
    Route::post("/cms/konflik/{id}/delete", [
        KonflikController::class,
        "destroy",
    ]);
    Route::get("/cms/konflik", [KonflikController::class, "index"]);
    Route::get("/cms/tambah-artikel/{id}", [ArtikelController::class, "add"]);
    Route::get("/cms/edit-artikel/{id}", [ArtikelController::class, "edit"]);
    Route::get("/cms/dashboard", [DashboardController::class, "index"]);

    // UniSharp LFM (abandoned, CVE-ridden): locked down via blockPathTraversal
    // (LFI) + storage/app/public/.htaccess + config/lfm.php allowlist.
    Route::group(
        [
            "prefix" => "/cms/fire-filemanager",
            "middleware" => [blockPathTraversal::class],
        ],
        function () {
            \UniSharp\LaravelFilemanager\Lfm::routes();
        },
    );

    // Administrator-only (role 0): user / group / instansi / perusahaan management
    Route::middleware([adminSession::class])->group(function () {
        Route::get("/cms/tambah-group", [GrupController::class, "add"]);
        Route::get("/cms/tambah-perusahaan", [
            PerusahaanController::class,
            "add",
        ]);
        Route::get("/cms/tambah-user", [UsersController::class, "add"]);
        Route::get("/cms/tambah-instansi", [InstansiController::class, "add"]);

        Route::get("/cms/editgroup/{id}", [GrupController::class, "edit"]);
        Route::get("/cms/editperusahaan/{id}", [
            PerusahaanController::class,
            "edit",
        ]);
        Route::get("/cms/edituser/{id}", [UsersController::class, "edit"]);
        Route::get("/cms/editinstansi/{id}", [
            InstansiController::class,
            "edit",
        ]);

        Route::get("/cms/group", [GrupController::class, "index"]);
        Route::get("/cms/perusahaan", [PerusahaanController::class, "index"]);
        Route::get("/cms/users", [UsersController::class, "index"]);
        Route::get("/cms/instansi", [InstansiController::class, "index"]);
    });
});

//redirect to dashboard if user has session to dashboard
Route::middleware([hasSession::class])->group(function () {
    Route::get("/cms/login", [DashboardController::class, "login"]);
});

//url to logout session (POST: GET logout is a forced-logout/CSRF vector)
Route::post("/cms/logout", function () {
    session()->flush();
    session()->regenerate();
    return redirect("/cms/login");
});
