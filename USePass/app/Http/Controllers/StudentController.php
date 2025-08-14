<?php

namespace App\Http\Controllers;


use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use App\Models\ParentCredential;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;
class StudentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'students_last_name' => 'required',
            'students_first_name' => 'required',
            'students_middle_initial' => 'nullable|string|max:1',
            'students_gender' => 'required',
            'students_id' => 'required|unique:students,students_id',
            'students_program' => 'required',
            'students_major' => 'nullable',
            'students_unit' => 'required',
            'students_email' => 'required|email|unique:students,students_email',
            'students_phone_num' => 'required',
            'students_profile_image' => 'nullable|image|max:5120',
        ]);
        $imagePath = null;

        if ($request->hasFile('students_profile_image')) {
            $image = $request->file('students_profile_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $destinationPath = public_path('profile_pictures');

            // Create the folder if it doesn't exist
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);
            $imagePath = 'profile_pictures/' . $imageName; // Relative path to store in DB
        }

        // Save student
        $student = Student::create([
            'students_id' => $request->students_id,
            'students_first_name' => $request->students_first_name,
            'students_middle_initial' => $request->students_middle_initial,
            'students_last_name' => $request->students_last_name,
            'students_gender' => $request->students_gender,
            'students_program' => $request->students_program,
            'students_major' => $request->students_major,
            'students_unit' => $request->students_unit,
            'students_email' => $request->students_email,
            'students_phone_num' => $request->students_phone_num,
            'students_profile_image' => $imagePath,
        ]);

        // Validate parent data
        $validatedParent = $request->validate([
            'parent_last_name' => 'required',
            'parent_first_name' => 'required',
            'parent_middle_initial' => 'nullable|string|max:1',
            'parent_phone_num' => 'required',
            'parent_email' => 'required|email|unique:parents,parent_email',
            'parent_relation' => 'required',
        ]);

        // Link student to parent using students_id
        $validatedParent['students_id'] = $student->students_id;

        // Save parent
        ParentCredential::create($validatedParent);
        $fullName = trim($student->students_first_name . ' ' . ($student->students_middle_initial ?? '') . ' ' . $student->students_last_name);

        $this->logActivity(
            $request->user()->id ?? null, // Assuming you have authenticated user, use null if not
            $request->user()->role ?? 'System', // Get user role or default to 'System'
            'Student Created',
            "New Student Added: Student ID: {$student->students_id}: {$fullName}"
        );

        return response()->json(['message' => 'Saved successfully.']);
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new StudentsImport, $request->file('file'));

        $this->logActivity(
            $request->user()->id ?? null, // Assuming you have authenticated user, use null if not
            $request->user()->role ?? 'System', // Get user role or default to 'System'
            'Student Import',
            "Importing Students Information"
        );

        return response()->json(['message' => 'Import successful']);
    }
    public function index()
    {
        $students = Student::all();
        return response()->json($students);
    }

    public function getStudentData(Request $request)
    {
        try {
            $request->validate(['students_id' => 'required|string']);

            $student = Student::with('parentInfo')
                ->where('students_id', $request->students_id)
                ->firstOrFail();

            $parent = $student->parentInfo;

            // Check if student and parent already have complete contact info
            $studentHasContact = !empty($student->students_email) && !empty($student->students_phone_num);
            $parentHasContact = false;

            if ($parent) {
                $parentHasContact = !empty($parent->parent_email) && !empty($parent->parent_phone_num);
            }

            // Determine what step to redirect to
            $redirectStep = 1; // Default
            $message = "Please complete your contact information.";

            if ($studentHasContact && $parentHasContact) {
                $redirectStep = 3;
                $message = "Your information is complete! Generating your barcode...";
            } elseif ($studentHasContact && !$parentHasContact) {
                $redirectStep = 2;
                $message = "Please update your parent/guardian contact information.";
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'student' => $student,
                    'parent' => $parent
                ],
                'status' => [
                    'student_has_contact' => $studentHasContact,
                    'parent_has_contact' => $parentHasContact,
                    'redirect_step' => $redirectStep,
                    'message' => $message
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error'
            ], 500);
        }
    }
    public function getCountsByCategory()
    {
        $categories = [
            'BSED' => ['Special Needs Education', 'Elementary Education', 'Early Childhood Education','English', 'Mathematics', 'Filipino'],
            'BSIT' => ['Information Security'],
            'BSABE' => ['Land and Water Resources', 'Machinery and Power', 'Process Engineering','Structure and Environment'],
            'BTVTED' => ['Agricultural Crops Technology', 'Animal Production'],
        ];

        $results = [];

        foreach ($categories as $label => $programs) {
            $count = Student::whereIn('students_major', $programs)->count();
            $results[] = [
                'course' => $label,
                'count' => $count,
            ];
        }

        return response()->json($results);
    }

    public function show($id)
    {
        $student = Student::where('students_id', $id)->first();

        if ($student) {
            // Compose full name manually since you store first, last, middle separately
            $fullName = trim($student->students_first_name . ' ' . ($student->students_middle_initial ?? '') . ' ' . $student->students_last_name);

            return response()->json([
                'exists' => true,
                'student' => [
                    'full_name' => $fullName,
                    'course' => $student->students_program,
                    'id_number' => $student->students_id,
                    'profile_image' => $student->students_profile_image,
                ],
            ]);
        } else {
            return response()->json(['exists' => false]);
        }
    }
    public function fetchStudentProfile($students_id)
    {
        $student = \App\Models\Student::select(
            'students_id as id',
            \DB::raw("CONCAT(students_first_name, ' ', students_middle_initial, ' ', students_last_name) as fullName"),
            'students_program as program',
            'students_profile_image as profileImage'
        )->where('students_id', $students_id)->first();

        return response()->json([
            'exists' => $student !== null,
            'student' => $student,
        ]);
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
