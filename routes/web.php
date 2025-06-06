<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\System\UserController;
use Inertia\Inertia;

Route::get('/', function () {
    return view('welcome');
});


// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard (contoh)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('system')->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('system.users.index');
            Route::get('/data', [UserController::class, 'data'])->name('system.users.data');
            Route::get('/create', [UserController::class, 'create'])->name('system.users.create');
            Route::post('/', [UserController::class, 'store'])->name('system.users.store');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('system.users.edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('system.users.update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('system.users.destroy');
        });
    });
});
