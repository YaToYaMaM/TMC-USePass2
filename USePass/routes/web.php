<?php
use App\Http\Controllers\Auth\CustomForgotPasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\FrontendControllers;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GuardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tighten\Ziggy\Ziggy;
use Inertia\Inertia;
use App\Http\Controllers\StudentRecordController;
use App\Http\Controllers\DashboardController;
//use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SpotController;
//use App\Http\Controllers\StudentReportController;
use App\Http\Controllers\Auth\FacultyRegistrationController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\FacultyRecordController;
use App\Http\Controllers\DatabaseController;

use Illuminate\Support\Facades\Log;

Route::get('/.well-known/{any}', function ($any) {
    if (str_contains($any, 'chrome.devtools')) {
        return response()->json(['message' => 'DevTools probe ignored'], 404);
    }
    Log::info('Chrome probe blocked:', ['path' => $any]);
    return response()->json(['message' => "$any not available"], 404);

})->where('any', '.*');


Route::middleware(['auth'])->group(function () {
    Route::get('/incident', [ReportController::class, 'incident'])->name('incident.index');
    Route::get('/spot-reports', [SpotController::class, 'spot']);
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
});



Route::get('/user', [App\Http\Controllers\FrontendControllers::class, 'user'])->name('user');
Route::get('/otp', [App\Http\Controllers\FrontendControllers::class, 'otp'])->name('otp');
Route::get('/scan', [App\Http\Controllers\FrontendControllers::class, 'scan'])->name('scan');
Route::get('/checking', [App\Http\Controllers\FrontendControllers::class, 'checking'])->name('checking');

Route::get('/gin', [App\Http\Controllers\FrontendControllers::class, 'gin'])->name('gin');
Route::get('/gout', [App\Http\Controllers\FrontendControllers::class, 'gout'])->name('gout');

//Students Registration
Route::post('/student/get-data', [StudentController::class, 'getStudentData']);
Route::get('/Details', [App\Http\Controllers\FrontendControllers::class, 'deets'])->name('deets');
Route::post('/student/send-otp', [CustomForgotPasswordController::class, 'sendStudentOtp'])->name('student.otp.send');
Route::post('/student/resend-otp', [CustomForgotPasswordController::class, 'resendStudentOtp'])->name('student.otp.resend');
Route::post('/student/verify-otp', [CustomForgotPasswordController::class, 'verifyOtp'])->name('student.otp.verify');
Route::post('/student/save-data', [CustomForgotPasswordController::class, 'saveStudentParentData'])->name('student.save.data');

//Faculty-Staff Registration
Route::get('/faculty-staff', [App\Http\Controllers\FrontendControllers::class, 'facultyStaffHome'])->name('faculty.home');
Route::get('/faculty/qr/{faculty_id}', [FacultyController::class, 'fetchForQR']);
Route::get('/faculty-staff/register', [App\Http\Controllers\FrontendControllers::class, 'facultyRegistration'])->name('faculty.register');
Route::post('/faculty/send-otp', [FacultyRegistrationController::class, 'sendFacultyOtp'])->name('faculty.otp.send');
Route::post('/faculty/resend-otp', [FacultyRegistrationController::class, 'resendFacultyOtp'])->name('faculty.otp.resend');
Route::get('/faculty/otp/verify', [FacultyRegistrationController::class, 'showFacultyOtpForm'])->name('faculty.otp.form');
Route::post('/faculty/verify-otp', [FacultyRegistrationController::class, 'verifyFacultyOtp'])->name('faculty.otp.verify');
Route::get('/faculty-staff/success', [App\Http\Controllers\FrontendControllers::class, 'facultySuccess'])->name('faculty.success');
Route::get('/facultystaffAttendance', [App\Http\Controllers\FrontendControllers::class, 'facultystaffAttendance'])->name('facultystaffattendance');
Route::get('/faculty/search-active', [FacultyRecordController::class, 'searchActiveFaculty']);

//Activity Logs
Route::get('/activity-logs', [ActivityLogController::class, 'index']);
Route::get('/activity-logs/user/{userId}', [ActivityLogController::class, 'getByUser']);
Route::get('/activity-logs/action/{action}', [ActivityLogController::class, 'getByAction']);
Route::post('/activity-logs', [ActivityLogController::class, 'store']);

// Admin Dashboard
Route::middleware(['auth', 'can:isAdmin'])->group(function () {
//    Route::get('/', [FrontendControllers::class, 'index'])->name('home');
    Route::get('/dashboard', [FrontendControllers::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/guard', [FrontendControllers::class, 'guard'])->name('guard');
    Route::get('/students', [FrontendControllers::class, 'students'])->name('students');
    Route::get('/faculty-and-staff', [FrontendControllers::class, 'facultynstaff'])->name('faculty-and-staff');
    Route::get('/statistics', [FrontendControllers::class, 'statistics'])->name('statistics');
    Route::get('/reports', [FrontendControllers::class, 'reports'])->name('reports');
    Route::get('/facultynstaffAttendance', [FrontendControllers::class, 'facultynstaffAttendance'])->name('reports');
    Route::get('/logs', [FrontendControllers::class, 'logs'])->name('logs');
    //Backup & Restore
    Route::get('/backupnRestore', [FrontendControllers::class, 'backupnRestore']);
});

// User Guard Dashboard
Route::middleware(['auth', 'can:isGuard'])->group(function () {
    Route::get('/', [FrontendControllers::class, 'ghome'])->name('guard.ghome');
//    Route::get('/scan', [FrontendControllers::class, 'scan'])->name('scan');
    Route::get('/GuardHome', [FrontendControllers::class, 'guardHome'])->name('guardHome');
    Route::get('/glog', [FrontendControllers::class, 'glog'])->name('glog');
});
//Route::get('/', function () {
//    return Inertia::render('Welcome', [
//        'canLogin' => Route::has('login'),
//        'canRegister' => Route::has('register'),
//        'laravelVersion' => Application::VERSION,
//        'phpVersion' => PHP_VERSION,
//    ]);
//});
//Route::middleware(['auth'])->group(function () {
//    Route::get('/incident', [FrontendControllers::class, 'incident'])->name('incident');
//});
// routes/web.php
Route::post('/incident-report', [ReportController::class, 'store']);

Route::post('/spot-report', [SpotController::class, 'store']);


Route::get('/incident-report/print', function () {
    return Inertia::render('Frontend/IncidentReportTemplate', [
        'report' => [
            'name' => request('name'),
            'date' => request('date'),
            'what' => request('what'),
            'who' => request('who'),
            'where' => request('where'),
            'when' => request('when'),
            'how' => request('how'),
            'why' => request('why'),
            'recommendation' => request('recommendation'),
        ],
    ]);
});
Route::get('/spot-report/print', function () {
    return Inertia::render('Frontend/SpotReportTemplate', [
        'report' => [
            'guardName' => request('guardName'),
            'date' => request('date'),
            'findings' => request('findings'),
            'time' => request('time'),
            'location' => request('location'),
            'actionTaken' => request('actionTaken'),
            'teamLeader' => request('teamLeader'),
            'departmentRepresentative' => request('departmentRepresentative'),
        ],
    ]);
});

Route::post('/students/import', [StudentController::class, 'import']);
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
Route::get('/students/list', [StudentController::class, 'index']);
Route::post('/users', [GuardController::class, 'store']);
Route::get('/guard/list', [GuardController::class, 'index']);
Route::put('/guard/{id}', [GuardController::class, 'update']);

// For manual attendance
Route::post('/manual-attendance', [StudentRecordController::class, 'manualAttendance']);
Route::get('/check-student-attendance', [StudentRecordController::class, 'checkStudentAttendance']);
// For searching students in the modal
Route::get('/search-students', [StudentRecordController::class, 'searchStudents']);
Route::get('/students/search-active', [StudentRecordController::class, 'searchActiveStudents']);

Route::get('/student-records', [StudentRecordController::class, 'fetchRecords']);
Route::get('/students-by-category', [StudentController::class, 'getCountsByCategory']);
Route::get('/getCounts', [DashboardController::class, 'getCounts']);
Route::get('/getProgramCategoryCounts', [DashboardController::class, 'getCountsByCategory']);
//Route::get('/getStats', [StudentRecordController::class, 'getStats']);
Route::get('/students/{students_id}', [StudentController::class, 'checkStudentExists']);
Route::get('/students/profile/{students_id}', [StudentController::class, 'fetchStudentProfile']);
Route::post('/students/log-scan', [StudentRecordController::class, 'log']);
//Route::get('/getStats', [DashboardController::class, 'getCounts']);


//FacultyStaff Records insert data and fetch data
Route::get('/faculty-records', [FacultyRecordController::class, 'fetchFacultyRecords']);
Route::post('/faculty-log', [FacultyRecordController::class, 'facultyLog']);

//FacultyStaff insert data and fetch data
Route::post('/faculty', [FacultyController::class, 'store']);
Route::get('/faculty', [FacultyController::class, 'index']);
Route::get('/faculty/{faculty_id}', [FacultyController::class, 'fetchFacultyProfile']);

//FacultyStaff Search and Manual attendance
Route::post('/manual-faculty-attendance', [FacultyRecordController::class, 'manualFacultyAttendance']);
Route::get('/check-faculty-attendance', [FacultyRecordController::class, 'checkFacultyAttendance']);
Route::get('/search-faculty', [FacultyRecordController::class, 'searchFaculty']);
Route::get('/search-active-faculty', [FacultyRecordController::class, 'searchActiveFaculty']);

//Database Backup and Restore

Route::get('/backupData', [DatabaseController::class, 'backupData'])->name('database.backup');
Route::post('/restoreData', [DatabaseController::class, 'restoreData'])->name('database.restore');

//Route::post('/change-password', [UserController::class, 'changePassword']);
//Route::get('/download-attendance-pdf', [StudentReportController::class, 'downloadPDF']);
//Route::get('/dashboard', function () {
//    return Inertia::render('Dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/otp/request', [CustomForgotPasswordController::class, 'sendOtp'])->name('otp.request');
Route::get('/otp/verify', [CustomForgotPasswordController::class, 'showOtpForm'])->name('otp.form');
Route::post('/otp/verify', [CustomForgotPasswordController::class, 'verifyOtp'])->name('otp.verify');
Route::post('/reset-password', [CustomForgotPasswordController::class, 'resetPassword'])->name('password.store');



//Route::get('/usepass-otp', function () {
//    return Inertia::render('Frontend/userOTP');
//});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::get('/js-routes', function () {
    return response()->json(new Ziggy);
});

require __DIR__.'/auth.php';
