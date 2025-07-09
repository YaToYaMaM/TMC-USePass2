<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class FrontendControllers extends Controller
{
    public function index(){
        return Inertia::render('Frontend/Home');
//        Route::get('/', function () {
//    return Inertia::render('Welcome', [
//        'canLogin' => Route::has('login'),
//        'canRegister' => Route::has('register'),
//        'laravelVersion' => Application::VERSION,
//        'phpVersion' => PHP_VERSION,
//    ]);
//});
    }

    public function user()
    {
        return Inertia::render('Frontend/EUserId');
    }

    public function otp()
    {
        return Inertia::render('Frontend/Eotp');
    }
    public function deets()
    {
        return Inertia::render('Frontend/Edetails');
    }

    public function dashboard()
    {
        return Inertia::render('Frontend/secDashboard');
    }

    public function guard()
    {
        return Inertia::render('Frontend/Guard');
//});
    }

    public function students()
    {
        return Inertia::render('Frontend/secStudents');
    }

    public function statistics()
    {
        return Inertia::render('Frontend/secStatistics');
    }

    public function reports()
    {
        return Inertia::render('Frontend/secReports');
    }

    public function logs()
    {
        return Inertia::render('Frontend/secLogs');
    }
}
