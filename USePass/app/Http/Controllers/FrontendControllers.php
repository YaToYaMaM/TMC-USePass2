<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Session;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FrontendControllers extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            if (auth()->user()->isAdmin()) {
                return Inertia::render('Frontend/secDashboard');
            }

            if (auth()->user()->isGuard()) {
                return Inertia::render('Frontend/Ghome');
            }
        }

        return redirect()->route('login');
    }


    public function user()
    {
        return Inertia::render('Frontend/EUserId');
    }

    public function otp()
    {
        return Inertia::render('Frontend/Eotp');
    }

//    public function scan()
//    {
//        return Inertia::render('Frontend/Gscan');
//    }
//
//
    public function guardHome()
    {
        return Inertia::render('Frontend/guardHome');
    }

    public function facultystaffAttendance()
    {
        return Inertia::render('Frontend/FacultyStaffAttendance');
    }

    public function checking()
    {
        return Inertia::render('Frontend/Checking');
    }

    public function ghome()
    {
        return Inertia::render('Frontend/Ghome');
    }

    public function gin()
    {
        return Inertia::render('Frontend/Gin');
    }

    public function gout()
    {
        return Inertia::render('Frontend/Gout');
    }


    public function glog()
    {
        return Inertia::render('Frontend/Glogs');
    }

    public function deets(Request $request)
    {
        $studentData = $request->studentData;
        $parentData = $request->parentData;
        $studentHasContact = $request->boolean('studentHasContact', false);
        $parentHasContact = $request->boolean('parentHasContact', false);

        $step = $request->get('step', 1);
        $mode = $request->get('mode', '');
        $requiresOtp = $request->boolean('requiresOtp', false);


        if (!$studentData && Session::get('verified_student_id')) {
            $student = Student::where('students_id', Session::get('verified_student_id'))->first();
            $studentData = $student;
            $parentData = $student ? $student->parentInfo : null;

            if ($studentData) {
                $studentHasContact = !empty($studentData->students_email) && !empty($studentData->students_phone_num);
            }
            if ($parentData) {
                $parentHasContact = !empty($parentData->parent_email) && !empty($parentData->parent_phone_num);
            }
        }

        if ($mode === 'parent_update' && $studentData) {
            Session::forget(['student_authenticated', 'verified_student_id', 'verified_student_email', 'verified_student_phone']);

            $step = 1;
            $requiresOtp = true;
        }

        return Inertia::render('Frontend/Edetails', [
            'studentData' => $studentData,
            'parentData' => $parentData,
            'studentHasContact' => $studentHasContact,
            'parentHasContact' => $parentHasContact,
            'step' => $step,
            'mode' => $mode,
            'requiresOtp' => $requiresOtp,
        ]);
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

    public function facultyStaffHome()
    {
        return Inertia::render('Frontend/FacultyStaffHome');
    }

    public function facultynstaff()
    {
        return Inertia::render('Frontend/adminFacultyNStaff');
    }
    public function facultynstaffAttendance()
    {
        return Inertia::render('Frontend/secFacultyReport');
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
    public function incident()
    {
        return Inertia::render('Frontend/secIncident');
    }

    public function facultyRegistration()
    {
        return Inertia::render('Frontend/FacultyRegistration');
    }

    public function facultySuccess(Request $request)
    {
        return Inertia::render('Frontend/FacultySuccess', [
            'facultyData' => $request->facultyData,
        ]);
    }

    public function backupnRestore(){
        return Inertia::render('Frontend/Backup&Restore');
    }
}
