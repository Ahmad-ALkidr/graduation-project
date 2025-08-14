<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin routes
Route::prefix('dashboard')->group(function () {

    // Guest routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminController::class, 'create'])->name('admin.login');
        Route::post('/login', [AdminController::class, 'store']);
    });

    // Authenticated Admin routes
    Route::middleware('auth:admin')->group(function () {

        // Dashboard
    Route::get('/home', [DashboardController::class, 'index'])->name('admin.dashboard');


        // Logout
        Route::post('/logout', [AdminController::class, 'destroy'])->name('admin.logout');

        // User Management (using the corrected controller name)
        Route::controller(AdminAuthController::class)->group(function () {
            // ✨ We removed the redundant middleware from these routes ✨
            Route::get('/list', 'show_list')->name('admin.list');
            Route::get('/create', 'show_create_form')->name('admin.show.create');
            Route::post('/create', 'create')->name('admin.create');
            Route::get('/account/{id}', 'account')->name('admin.account');
            Route::post('/account/edit', 'edit')->name('admin.edit');
            Route::post('/account/change-password', 'change_password')->name('admin.change.password');
            Route::get('/account/delete/{user}', 'delete')->name('admin.delete'); // ✨ Corrected route
        });
    });
});
// --- User Management Routes ---
// 2. Use a nested prefix for better organization (e.g., /dashboard/users/...)
Route::prefix('users')->name('admin.users.')->controller(UserManagementController::class)->group(function () {

    // 3. The 'auth:admin' middleware is already applied by the parent group

    Route::get('/', 'show_list')->name('admin.list');
    Route::get('/create', 'show_create_form')->name('admin.create');
    Route::post('/create', 'store')->name('admin.store');

    // 4. Use Route Model Binding with '{user}' to match the controller methods
    Route::get('/account/{user}', 'account')->name('admin.account');
    Route::post('/account/update/{user}', 'update')->name('admin.update');
    Route::get('/account/delete/{user}', 'delete')->name('admin.delete');
    // ✨ --- NEW: Route for students --- ✨
    Route::get('/students', 'show_students')->name('list.students');

    // ✨ --- NEW: Route for academics --- ✨
    Route::get('/academics', 'show_academics')->name('list.academics');

    // You might want a separate route for the password change form/handler
    // Route::post('/account/change-password/{user}', 'change_password')->name('change-password');
});
