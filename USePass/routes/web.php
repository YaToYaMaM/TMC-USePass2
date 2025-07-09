<?php

use App\Http\Controllers\FrontendControllers;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


//Route::get('/', [App\Http\Controllers\FrontendControllers::class, 'index'])->name('home');
Route::get('/user', [App\Http\Controllers\FrontendControllers::class, 'user'])->name('user');
Route::get('/otp', [App\Http\Controllers\FrontendControllers::class, 'otp'])->name('otp');
Route::get('/Details', [App\Http\Controllers\FrontendControllers::class, 'deets'])->name('deets');

Route::get('/dashboard', [FrontendControllers::class, 'dashboard'])->name('dashboard');
Route::get('/guard', [FrontendControllers::class, 'guard'])->name('guard');
Route::get('/students', [FrontendControllers::class, 'students'])->name('students');
Route::get('/statistics', [FrontendControllers::class, 'statistics'])->name('statistics');
Route::get('/reports', [FrontendControllers::class, 'reports'])->name('reports');
Route::get('/logs', [FrontendControllers::class, 'logs'])->name('logs');

//Route::get('/', function () {
//    return Inertia::render('Welcome', [
//        'canLogin' => Route::has('login'),
//        'canRegister' => Route::has('register'),
//        'laravelVersion' => Application::VERSION,
//        'phpVersion' => PHP_VERSION,
//    ]);
//});

//Route::get('/dashboard', function () {
//    return Inertia::render('Dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//Route::get('/usepass-otp', function () {
//    return Inertia::render('Frontend/userOTP');
//});

require __DIR__.'/auth.php';
