<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Faculty;
use Maatwebsite\Excel\Facades\Excel;

class FacultyController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => 'required|string|unique:facultyStaff,faculty_id',
            'faculty_first_name' => 'required|string|max:100',
            'faculty_last_name' => 'required|string|max:100',
            'faculty_middle_initial' => 'nullable|string|max:1',
            'faculty_gender' => 'required|string|max:50',
            'faculty_department' => 'required|string|max:100',
            'faculty_unit' => 'required|in:Tagum,Mabini',
            'faculty_email' => 'required|email|unique:facultyStaff,faculty_email|max:100',
            'faculty_phone_num' => 'required|string|max:20',
            'faculty_profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        $imagePath = null;
        if ($request->hasFile('faculty_profile_image')) {
            $image = $request->file('faculty_profile_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $destinationPath = public_path('profile_pictures');

            // Create the folder if it doesn't exist
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);
            $imagePath = 'faculty_pictures/' . $imageName; // Relative path to store in DB
        }
        Faculty::create([
            'faculty_id' => $request->faculty_id,
            'faculty_last_name' => $request->faculty_last_name,
            'faculty_first_name' => $request->faculty_first_name,
            'faculty_middle_initial' => $request->faculty_middle_initial,
            'faculty_gender'=> $request->faculty_gender,
            'faculty_department'=> $request->faculty_department,
            'faculty_unit'=> $request->faculty_unit,
            'faculty_email'=> $request->faculty_email,
            'faculty_phone_num' => $request->faculty_phone_num,
            'faculty_profile_image' => $imagePath,
        ]);

        $this->logActivity(
            $request->user()->id ?? null, // Assuming you have authenticated user, use null if not
            $request->user()->role ?? 'System', // Get user role or default to 'System'
            'Faculty/Staff Created',
            "New Faculty/Staff Added: ID: {$request->faculty_id}, {$request->faculty_first_name} {$request->faculty_last_name}"
        );

        return response()->json(['message' => 'Guard created successfully.']);

    }

    public function index(){
        $faculty = Faculty::all();
        return response()->json($faculty);

    }

    public function fetchFacultyProfile($faculty_id)
    {
        $faculty = Faculty::select(
            'faculty_id as id',
            \DB::raw("CONCAT(faculty_first_name, ' ', faculty_middle_initial, ' ', faculty_last_name) as fullName"),
            'faculty_department as program',
            'faculty_profile_image as profileImage'
        )->where('faculty_id', $faculty_id)->first();

        return response()->json([
            'exists' => $faculty !== null,
            'faculty' => $faculty,
        ]);
    }

    public function fetchForQR($faculty_id)
    {
        try {
            $faculty = Faculty::where('faculty_id', $faculty_id)->first();

            if ($faculty) {
                return response()->json([
                    'success' => true,
                    'faculty' => $faculty
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Faculty not found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving faculty data'
            ], 500);
        }
    }



    private function logActivity($userId, $role, $action, $description)
    {
        try {
            ActivityLog::create([
                'user_id' => $userId,
                'role' => $role,
                'log_action' => $action,
                'log_description' => $description,
            ]);
        } catch (\Exception $e) {
            // Log to Laravel's default log if activity logging fails
            \Log::error('Failed to create activity log: ' . $e->getMessage());
        }
    }
}
