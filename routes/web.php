<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\System\UserController;
use Inertia\Inertia;
use App\Http\Controllers\System\RoleController;
use App\Http\Controllers\System\PermissionController;
use App\Http\Controllers\System\AuditController;
use App\Http\Controllers\System\MenuController;

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
        // User Management (Admin only)
        Route::prefix('users')->middleware('role:Admin')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('system.users.index');
            Route::get('/data', [UserController::class, 'data'])->name('system.users.data');
            Route::get('/create', [UserController::class, 'create'])->name('system.users.create');
            Route::post('/', [UserController::class, 'store'])->name('system.users.store');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('system.users.edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('system.users.update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('system.users.destroy');
        });

        // Role Management (Admin only)
        Route::prefix('roles')->middleware('role:Admin')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('system.roles.index');
            Route::get('/data', [RoleController::class, 'data'])->name('system.roles.data');
            Route::get('/create', [RoleController::class, 'create'])->name('system.roles.create');
            Route::post('/', [RoleController::class, 'store'])->name('system.roles.store');
            Route::get('/{id}/edit', [RoleController::class, 'edit'])->name('system.roles.edit');
            Route::put('/{id}', [RoleController::class, 'update'])->name('system.roles.update');
            Route::delete('/{id}', [RoleController::class, 'destroy'])->name('system.roles.destroy');
        });

        // Permission Management (Admin only)
        Route::prefix('permissions')->middleware('role:Admin')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('system.permissions.index');
            Route::get('/data', [PermissionController::class, 'data'])->name('system.permissions.data');
            Route::get('/create', [PermissionController::class, 'create'])->name('system.permissions.create');
            Route::post('/', [PermissionController::class, 'store'])->name('system.permissions.store');
            Route::get('/{id}/edit', [PermissionController::class, 'edit'])->name('system.permissions.edit');
            Route::put('/{id}', [PermissionController::class, 'update'])->name('system.permissions.update');
            Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('system.permissions.destroy');
        });

        // Audit Logs (Admin only)
        Route::prefix('audits')->middleware('role:Admin')->group(function () {
            Route::get('/', [AuditController::class, 'index'])->name('system.audits.index');
            Route::get('/data', [AuditController::class, 'data'])->name('system.audits.data');
            Route::get('/{audit}', [AuditController::class, 'show'])->name('system.audits.show');
        });

        // Menu Management (Admin only)
        Route::prefix('menus')->middleware('role:Admin')->group(function () {
            Route::get('/', [MenuController::class, 'index'])->name('system.menus.index');
            Route::get('/create', [MenuController::class, 'create'])->name('system.menus.create');
            Route::post('/', [MenuController::class, 'store'])->name('system.menus.store');
            Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('system.menus.edit');
            Route::put('/{menu}', [MenuController::class, 'update'])->name('system.menus.update');
            Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('system.menus.destroy');
            Route::post('/update-order', [MenuController::class, 'updateOrder'])->name('system.menus.updateOrder');
        });
    });

    // API endpoint untuk sidebar menu
    Route::get('/api/menus', [MenuController::class, 'getMenusJson'])->name('api.menus');
});
