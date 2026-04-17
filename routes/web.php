<?php

use App\Http\Controllers\VideoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\CategoryController::class, 'index'])->name('dashboard');
    
    // Categorías y Videos (Cualquier usuario logeado gestiona lo suyo)
    Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'show'])->name('categories.show');
    Route::post('/categories/{category}/unlock', [\App\Http\Controllers\CategoryController::class, 'unlock'])->name('categories.unlock');
    
    Route::post('/categories', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'store'])->name('categories.store');
    Route::patch('/categories/{category}', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    
    Route::post('/video/upload', [\App\Http\Controllers\VideoController::class, 'upload'])->name('video.upload');
    Route::delete('/video/{video}', [\App\Http\Controllers\VideoController::class, 'destroy'])->name('video.destroy');
    Route::get('/video/{video}/stream', [\App\Http\Controllers\VideoController::class, 'stream'])->name('video.stream');

    // Administración (Solo para administrador principal)
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::get('/admin/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('admin.users.store');
        Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
