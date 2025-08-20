<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LibraryManagementController;
use App\Http\Controllers\Admin\LibraryStructureController;
use App\Http\Controllers\Admin\manage_users\AdminAuthController;
use App\Http\Controllers\Admin\manage_users\AdminController;
use App\Http\Controllers\Admin\manage_users\UserManagementController;
use App\Http\Controllers\Admin\SubjectManagementController;
use App\Http\Controllers\Api\CollegeController;
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
// --- College Management Routes ---
Route::prefix('colleges')->name('admin.colleges.')->controller(CollegeController::class)->group(function () {
    Route::get('/', 'index')->name('list');
    Route::post('/store', 'store')->name('store');
    Route::post('/delete/{college}', 'destroy')->name('delete');
});

// مسارات إدارة ملفات المكتبة
Route::prefix('library')->name('admin.library-files.')->controller(LibraryManagementController::class)->group(function () {
    Route::get('/library/pending', [LibraryManagementController::class, 'showPendingFiles'])->name('pending');
    Route::post('/approve/{file}', [LibraryManagementController::class, 'approve'])->name('approve');
    Route::post('/delete/{file}', [LibraryManagementController::class, 'destroy'])->name('delete');
    Route::get('/download/{file}', [LibraryManagementController::class, 'downloadFile'])->name('download');
    // ✨ --- for viewing the file --- ✨
    Route::get('/view/{file}', [LibraryManagementController::class, 'viewFile'])->name('view');
    // ✨ --- for approved files --- ✨
    Route::get('/approved', [LibraryManagementController::class, 'showApprovedFiles'])->name('approved');

});

// --- Library Structure Management ---
Route::prefix('library-structure')->name('admin.library.structure.')->group(function () {
    Route::get('/', [LibraryStructureController::class, 'index'])->name('index');

    // College Routes
    Route::post('/colleges', [LibraryStructureController::class, 'storeCollege'])->name('colleges.store');
    // ... other college routes

    // ✨ NEW: Department Route
    Route::post('/departments', [LibraryStructureController::class, 'storeDepartment'])->name('departments.store');

    // ✨ NEW: Subject/Course Route
    Route::post('/subjects', [LibraryStructureController::class, 'storeSubject'])->name('subjects.store');
    Route::post('/materials', [LibraryStructureController::class, 'storeMaterial'])->name('materials.store');


    // You would add routes for departments, subjects, etc. here
});


// --- Subject Management Routes ---
Route::prefix('subjects')->name('admin.subjects.')->controller(SubjectManagementController::class)->group(function () {
    Route::get('/', 'index')->name('list');
    Route::post('/store', 'store')->name('store');
    Route::post('/delete/{subject}', 'destroy')->name('delete');
});


// Route::get('/check-time', function () {
//     $laravelTime = now()->toDateTimeString();
//     $phpTime = date('Y-m-d H:i:s');
//     $timezone = config('app.timezone');

//     return "
//         <h1>Time Check</h1>
//         <p>Laravel Time (now()): <strong>{$laravelTime}</strong></p>
//         <p>PHP Time (date()): <strong>{$phpTime}</strong></p>
//         <p>Application Timezone: <strong>{$timezone}</strong></p>
//     ";
// });
