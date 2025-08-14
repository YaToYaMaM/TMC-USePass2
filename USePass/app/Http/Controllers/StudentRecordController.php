<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ParentCredential;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentAttendanceReport;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class StudentRecordController extends Controller
{
    public function fetchRecords(Request $request)
    {
        $query = DB::table('students_records')
            ->join('students', 'students.students_id', '=', 'students_records.students_id')
            ->select(
                'students.students_id as student_id',
                DB::raw("CONCAT(students.students_first_name, ' ', students.students_middle_initial, '. ', students.students_last_name) as name"),
                'students.students_program',
                'students.students_major',
                'students_unit',
                DB::raw('DATE(students_records.created_at) as date'),
                'students_records.record_in',
                'students_records.record_out'
            );

        if ($request->filled('date')) {
            $query->whereDate('students_records.created_at', $request->date);
        }

        if ($request->filled('program')) {
            $query->where('students.students_program', $request->program);
        }

        // Sort by latest time in (most recent first)
        $query->orderBy('students_records.record_in', 'desc');

        $records = $query->get();

        return response()->json($records);
    }

    /**
     * Search for students currently inside the campus
     * (students who have checked in but not checked out today)
     */
    public function searchActiveStudents(Request $request)
    {
        $query = $request->get('query');

        if (empty($query) || strlen(trim($query)) < 2) {
            return response()->json([
                'students' => [],
                'message' => 'Query must be at least 2 characters long.'
            ]);
        }

        try {
            $students = DB::table('students_records')
                ->join('students', 'students.students_id', '=', 'students_records.students_id')
                ->select(
                    'students.students_id as id',
                    'students.students_id as id_number',
                    DB::raw("CONCAT(students.students_first_name, ' ', students.students_middle_initial, '. ', students.students_last_name) as full_name"),
                    'students.students_program as program',
                    'students.students_major as major',
                    'students.students_unit as unit',
                    'students.students_profile_image as profile',
                    'students_records.record_in',
                    'students_records.record_out'
                )
                // Only get records from today
                ->whereDate('students_records.created_at', today())
                // Only students who have checked in but not out (record_out is null)
                ->whereNotNull('students_records.record_in')
                ->whereNull('students_records.record_out')
                // Search by name, ID, or program
                ->where(function($q) use ($query) {
                    $q->where(DB::raw("CONCAT(students.students_first_name, ' ', students.students_middle_initial, '. ', students.students_last_name)"), 'LIKE', "%{$query}%")
                        ->orWhere('students.students_id', 'LIKE', "%{$query}%")
                        ->orWhere('students.students_program', 'LIKE', "%{$query}%")
                        ->orWhere('students.students_major', 'LIKE', "%{$query}%");
                })
                ->orderBy('students.students_first_name')
                ->limit(10) // Limit to 10 results for performance
                ->get();

            return response()->json([
                'students' => $students,
                'count' => $students->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Student search error: ' . $e->getMessage());

            return response()->json([
                'students' => [],
                'message' => 'An error occurred while searching students.',
                'error' => $e->getMessage() // Remove this in production
            ], 500);
        }
    }

    /**
     * Manual attendance logging function
     */
    public function manualAttendance(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,students_id',
            'date' => 'required|date',
            'time' => 'required|string', // Format: HH:MM
            'type' => 'required|in:time_in,time_out',
        ]);

        $studentsId = $request->input('student_id');
        $date = $request->input('date');
        $time = $request->input('time');
        $type = $request->input('type');

        try {
            $student = Student::where('students_id', $studentsId)->first();
            $parent = ParentCredential::where('students_id', $studentsId)->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found.'], 404);
            }

            // Combine date and time to create a Carbon instance
            $dateTime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time, 'Asia/Manila');

            if ($type === 'time_in') {
                // Check if there's already a time-in record for this date without time-out
                $existingRecord = StudentRecord::where('students_id', $studentsId)
                    ->whereDate('record_in', $date)
                    ->whereNull('record_out')
                    ->first();

                if ($existingRecord) {
                    return response()->json([
                        'error' => 'Student already has an active time-in record for this date. Please time-out first.'
                    ], 422);
                }

                // Create new time-in record
                $record = StudentRecord::create([
                    'students_id' => $studentsId,
                    'record_in' => $dateTime,
                    'record_out' => null,
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ]);

                $status = 'Time In';
                $this->logActivity(
                    $request->user()->id ?? null,
                    $request->user()->role ?? 'System',
                    'Manual Student Time In',
                    "Student ID: {$student->students_id}, Manual Time-In at {$dateTime->format('Y-m-d H:i:s')} by Guard ID: " . ($request->user()->id ?? 'System')
                );

            } else { // time_out
                // Find the latest time-in record for this date that doesn't have time-out
                $record = StudentRecord::where('students_id', $studentsId)
                    ->whereDate('record_in', $date)
                    ->whereNull('record_out')
                    ->latest('record_in')
                    ->first();

                if (!$record) {
                    return response()->json([
                        'error' => 'No active time-in record found for this student on the specified date.'
                    ], 422);
                }

                // Check if time-out is after time-in
                if ($dateTime->lt($record->record_in)) {
                    return response()->json([
                        'error' => 'Time-out cannot be earlier than time-in.'
                    ], 422);
                }

                // Update the record with time-out
                $record->update([
                    'record_out' => $dateTime,
                    'updated_at' => Carbon::now('Asia/Manila'),
                ]);

                $status = 'Time Out';
                $this->logActivity(
                    $request->user()->id ?? null,
                    $request->user()->role ?? 'System',
                    'Manual Student Time Out',
                    "Student ID: {$student->students_id}, Manual Time-Out at {$dateTime->format('Y-m-d H:i:s')} by Guard ID: " . ($request->user()->id ?? 'System')
                );
            }

            // Send email notification if parent exists
            if ($parent && $parent->parent_email) {
                $this->sendAttendanceEmail($student, $parent, $status, $dateTime);
            }

            return response()->json([
                'success' => true,
                'message' => "{$status} recorded successfully for {$student->students_first_name} {$student->students_last_name}",
                'status' => strtolower(str_replace(' ', '_', $status)),
                'time' => $dateTime->format('Y-m-d H:i:s'),
                'student' => [
                    'id' => $student->students_id,
                    'name' => "{$student->students_first_name} {$student->students_middle_initial}. {$student->students_last_name}",
                    'program' => $student->students_program,
                    'unit' => $student->students_unit,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Manual attendance error: ' . $e->getMessage());

            return response()->json([
                'error' => 'An error occurred while recording attendance.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search all students (for manual attendance modal)
     */
    public function searchStudents(Request $request)
    {
        $query = $request->get('query');

        if (empty($query) || strlen(trim($query)) < 2) {
            return response()->json([
                'students' => [],
                'message' => 'Query must be at least 2 characters long.'
            ]);
        }

        try {
            $students = DB::table('students')
                ->select(
                    'students.students_id as id',
                    'students.students_id as id_number',
                    DB::raw("CONCAT(students.students_first_name, ' ', COALESCE(students.students_middle_initial, ''), '. ', students.students_last_name) as name"),
                    'students.students_program as program',
                    'students.students_major as major',
                    'students.students_unit as unit',
                    'students.students_profile_image as profile'
                )
                // Search by name, ID, program, or major
                ->where(function($q) use ($query) {
                    $q->where(DB::raw("CONCAT(students.students_first_name, ' ', COALESCE(students.students_middle_initial, ''), '. ', students.students_last_name)"), 'LIKE', "%{$query}%")
                        ->orWhere('students.students_id', 'LIKE', "%{$query}%")
                        ->orWhere('students.students_program', 'LIKE', "%{$query}%")
                        ->orWhere('students.students_major', 'LIKE', "%{$query}%");
                })
                ->orderBy('students.students_first_name')
                ->limit(15) // Limit to 15 results for performance
                ->get();

            return response()->json([
                'students' => $students,
                'count' => $students->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Student search error: ' . $e->getMessage());

            return response()->json([
                'students' => [],
                'message' => 'An error occurred while searching students.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkStudentAttendance(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'date' => 'required|date'
        ]);

        $studentId = $request->input('student_id');
        $date = $request->input('date');

        try {
            // Get the most recent record for the student on the specified date
            // This is the key fix - order by created_at DESC and get the latest
            $record = DB::table('students_records')
                ->where('students_id', $studentId)
                ->whereDate('created_at', $date)
                ->orderBy('created_at', 'desc') // Get the most recent record
                ->first();

            if (!$record) {
                return response()->json([
                    'record' => null,
                    'message' => 'No attendance record found for this date.'
                ]);
            }

            return response()->json([
                'record' => [
                    'id' => $record->id ?? null,
                    'student_id' => $record->students_id,
                    'time_in' => $record->record_in,
                    'time_out' => $record->record_out,
                    'date' => $record->created_at,
                    'status' => $this->getAttendanceStatus($record->record_in, $record->record_out)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Check attendance error: ' . $e->getMessage());
            Log::error('Student ID: ' . $studentId . ', Date: ' . $date);

            return response()->json([
                'record' => null,
                'message' => 'An error occurred while checking attendance status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to determine attendance status
     */
    private function getAttendanceStatus($timeIn, $timeOut)
    {
        $hasTimeIn = $timeIn && $timeIn !== 'N/A' && !is_null($timeIn);
        $hasTimeOut = $timeOut && $timeOut !== 'N/A' && !is_null($timeOut);

        if ($hasTimeIn && !$hasTimeOut) {
            return 'present'; // Student is currently inside
        } elseif ($hasTimeIn && $hasTimeOut) {
            return 'completed'; // Student has completed attendance
        } else {
            return 'no_record'; // No valid record
        }
    }
    public function index(Request $request)
    {
        $date = $request->query('date');

        if (empty($date)) {
            return response()->json([
                'message' => 'Date parameter is required.'
            ], 400);
        }

        $records = StudentRecord::whereDate('record_in', $date)->get();

        return response()->json($records);
    }

    public function log(Request $request)
    {
        $request->validate([
            'students_id' => 'required|exists:students,students_id',
        ]);

        $studentsId = $request->input('students_id');
        $now = Carbon::now('Asia/Manila');

        $student = Student::where('students_id', $studentsId)->first();
        $parent = ParentCredential::where('students_id', $studentsId)->first();

        if (!$student || !$parent || !$parent->parent_email) {
            return response()->json(['error' => 'Missing student or parent data.'], 422);
        }

        // Get today's latest record
        $latestRecord = StudentRecord::where('students_id', $studentsId)
            ->whereDate('record_in', $now->toDateString())
            ->latest()
            ->first();

        $status = '';

        if (!$latestRecord || $latestRecord->record_out !== null) {
            // Time In
            StudentRecord::create([
                'students_id' => $studentsId,
                'record_in' => $now,
                'record_out' => null,
            ]);
            $status = 'Time In';
            $this->logActivity(
                $request->user()->id ?? null,
                $request->user()->role ?? 'System',
                'Student Time In',
                "Student ID: {$student->students_id}, Time-In By Guard ID:{$request->user()->id}"
            );
        } else {
            // Time Out
            $latestRecord->update([
                'record_out' => $now,
            ]);
            $status = 'Time Out';
            $this->logActivity(
                $request->user()->id ?? null,
                $request->user()->role ?? 'System',
                'Student Time Out',
                "Student ID: {$student->students_id}, Time-Out By Guard ID:{$request->user()->id}"
            );
        }

        // Send email notification
        $this->sendAttendanceEmail($student, $parent, $status, $now);

        return response()->json([
            'status' => strtolower($status),
            'time' => $now->toDateTimeString(),
        ]);
    }

    /**
     * Send attendance email notification
     */
    private function sendAttendanceEmail($student, $parent, $status, $dateTime)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'usepasstmc.system@gmail.com';
            $mail->Password = 'rhkwujluyfwnaxpy';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('usepasstmc.system@gmail.com', 'USePass System');
            $mail->addAddress($parent->parent_email);

            $mail->isHTML(true);
            $mail->Subject = 'Student Attendance Report';

            $studentName = "{$student->students_first_name} {$student->students_last_name}";
            $formattedTime = $dateTime->format('h:i A');
            $formattedDate = $dateTime->format('F j, Y');

            $logoPath = public_path('images/usep-logo-small.png');
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'useplogo');
            }

            $mail->Body = "
            <div style='font-family: Arial, sans-serif;'>
                <div style='text-align: center; margin-bottom: 10px;'>
                    <img src='cid:useplogo' alt='USePASS Logo' style='width: 120px;'>
                    <h2 style='color: #2d3748;'>USePASS</h2>
                </div>
                <p><strong>Date:</strong> {$formattedDate}</p>
                <p style='margin-top: 20px;'>Dear Parent,</p>
                <p>Your child <strong>{$studentName}</strong> has recorded a <strong>{$status}</strong> at <strong>{$formattedTime}</strong>.</p>
                <p>Thank you for using USePass.</p>
            </div>
            ";

            $mail->send();
        } catch (Exception $e) {
            Log::error('Email failed to send: ' . $e->getMessage());
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
            Log::error('Failed to create activity log: ' . $e->getMessage());
        }
    }
}
