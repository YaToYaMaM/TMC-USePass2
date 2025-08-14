<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FacultyRecords;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FacultyRecordController extends Controller
{
    public function fetchFacultyRecords(Request $request)
    {
        $query = DB::table('facultystaff_records')
            ->join('facultystaff', 'facultystaff.faculty_id', '=', 'facultystaff_records.faculty_id')
            ->select(
                'facultystaff.faculty_id as faculty_id',
                DB::raw("CONCAT(facultystaff.faculty_first_name, ' ', facultystaff.faculty_middle_initial, '. ', facultystaff.faculty_last_name) as name"),
                'facultystaff.faculty_department',
                'facultystaff.faculty_unit',
                DB::raw('DATE(facultystaff_records.created_at) as date'),
                'facultystaff_records.record_in',
                'facultystaff_records.record_out'
            );

        if ($request->filled('date')) {
            $query->whereDate('facultystaff_records.created_at', $request->date);
        }

        if ($request->filled('department')) {
            $query->where('facultystaff.faculty_department', $request->department);
        }

        // Sort by latest time in (most recent first)
        $query->orderBy('facultystaff_records.record_in', 'desc');

        $records = $query->get();

        return response()->json($records);
    }

    public function searchActiveFaculty(Request $request)
    {
        $query = $request->get('query');

        if (empty($query) || strlen(trim($query)) < 2) {
            return response()->json([
                'faculty' => [],
                'message' => 'Query must be at least 2 characters long.'
            ]);
        }

        try {
            $faculty = DB::table('facultystaff_records')
                ->join('facultystaff', 'facultystaff.faculty_id', '=', 'facultystaff_records.faculty_id')
                ->select(
                    'facultystaff.faculty_id as id',
                    'facultystaff.faculty_id as employee_id',
                    DB::raw("CONCAT(facultystaff.faculty_first_name, ' ',
                CASE
                    WHEN facultystaff.faculty_middle_initial IS NOT NULL AND facultystaff.faculty_middle_initial != ''
                    THEN CONCAT(facultystaff.faculty_middle_initial, '. ')
                    ELSE ''
                END,
                facultystaff.faculty_last_name) as name"),
                    'facultystaff.faculty_first_name as first_name',
                    'facultystaff.faculty_last_name as last_name',
                    'facultystaff.faculty_middle_initial as middle_initial',
                    'facultystaff.faculty_department as department',
                    'facultystaff.faculty_unit as unit',
                    'facultystaff.faculty_email as email',
                    'facultystaff.faculty_phone_num as phone',
                    'facultystaff.faculty_profile_image as profile',
                    'facultystaff_records.record_in',
                    'facultystaff_records.record_out',
                    DB::raw('DATE(facultystaff_records.created_at) as date')
                )
                ->whereDate('facultystaff_records.created_at', today())
                ->whereNotNull('facultystaff_records.record_in')
                ->whereNull('facultystaff_records.record_out')
                ->where(function($q) use ($query) {
                    $q->where(DB::raw("CONCAT(facultystaff.faculty_first_name, ' ',
                    CASE
                        WHEN facultystaff.faculty_middle_initial IS NOT NULL AND facultystaff.faculty_middle_initial != ''
                        THEN CONCAT(facultystaff.faculty_middle_initial, '. ')
                        ELSE ''
                    END,
                    facultystaff.faculty_last_name)"), 'LIKE', "%{$query}%")
                        ->orWhere('facultystaff.faculty_id', 'LIKE', "%{$query}%")
                        ->orWhere('facultystaff.faculty_department', 'LIKE', "%{$query}%")
                        ->orWhere('facultystaff.faculty_unit', 'LIKE', "%{$query}%")
                        ->orWhere('facultystaff.faculty_email', 'LIKE', "%{$query}%");
                })
                ->orderBy('facultystaff.faculty_first_name')
                ->limit(10)
                ->get()
                ->map(function($faculty) {
                    return [
                        'id' => $faculty->id,
                        'employee_id' => $faculty->employee_id,
                        'name' => $faculty->name,
                        'first_name' => $faculty->first_name,
                        'last_name' => $faculty->last_name,
                        'middle_initial' => $faculty->middle_initial,
                        'department' => $faculty->department,
                        'unit' => $faculty->unit,
                        'email' => $faculty->email,
                        'phone' => $faculty->phone,
                        'profile' => $faculty->profile ? asset('storage/faculty_profiles/' . $faculty->profile) : null,
                        'record_in' => $faculty->record_in,
                        'record_out' => $faculty->record_out,
                        'date' => $faculty->date,
                        'status' => 'Present'
                    ];
                });

            return response()->json([
                'faculty' => $faculty,
                'count' => $faculty->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Faculty search error: ' . $e->getMessage());

            return response()->json([
                'faculty' => [],
                'message' => 'An error occurred while searching faculty.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual attendance logging function - Updated to match student logic
     */
    public function manualFacultyAttendance(Request $request)
    {
        $request->validate([
            'faculty_id' => 'required|exists:facultystaff,faculty_id',
            'date' => 'required|date',
            'time' => 'required|string', // Format: HH:MM
            'type' => 'required|in:time_in,time_out',
        ]);

        $facultyId = $request->input('faculty_id');
        $date = $request->input('date');
        $time = $request->input('time');
        $type = $request->input('type');

        try {
            $faculty = Faculty::where('faculty_id', $facultyId)->first();

            if (!$faculty) {
                return response()->json(['error' => 'Faculty not found.'], 404);
            }

            // Combine date and time to create a Carbon instance
            $dateTime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time, 'Asia/Manila');

            if ($type === 'time_in') {
                // Check if there's already a time-in record for this date without time-out
                $existingRecord = FacultyRecords::where('faculty_id', $facultyId)
                    ->whereDate('created_at', $date) // Changed to match student logic
                    ->whereNotNull('record_in')
                    ->whereNull('record_out')
                    ->first();

                if ($existingRecord) {
                    return response()->json([
                        'error' => 'Faculty already has an active time-in record for this date. Please time-out first.'
                    ], 422);
                }

                // Create new time-in record
                $record = FacultyRecords::create([
                    'faculty_id' => $facultyId,
                    'record_in' => $dateTime,
                    'record_out' => null,
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ]);

                $status = 'Time In';
                $this->logActivity(
                    $request->user()->id ?? null,
                    $request->user()->role ?? 'System',
                    'Manual Faculty Time In',
                    "Faculty ID: {$faculty->faculty_id}, Manual Time-In at {$dateTime->format('Y-m-d H:i:s')} by Guard ID: " . ($request->user()->id ?? 'System')
                );

            } else { // time_out
                // Find the latest time-in record for this date that doesn't have time-out
                $record = FacultyRecords::where('faculty_id', $facultyId)
                    ->whereDate('created_at', $date) // Changed to match student logic
                    ->whereNotNull('record_in')
                    ->whereNull('record_out')
                    ->latest('created_at') // Changed to match student logic
                    ->first();

                if (!$record) {
                    return response()->json([
                        'error' => 'No active time-in record found for this faculty on the specified date.'
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
                    'Manual Faculty Time Out',
                    "Faculty ID: {$faculty->faculty_id}, Manual Time-Out at {$dateTime->format('Y-m-d H:i:s')} by Guard ID: " . ($request->user()->id ?? 'System')
                );
            }

            return response()->json([
                'success' => true,
                'message' => "{$status} recorded successfully for {$faculty->faculty_first_name} {$faculty->faculty_last_name}",
                'status' => strtolower(str_replace(' ', '_', $status)),
                'time' => $dateTime->format('Y-m-d H:i:s'),
                'faculty' => [
                    'id' => $faculty->faculty_id,
                    'name' => "{$faculty->faculty_first_name} {$faculty->faculty_middle_initial}. {$faculty->faculty_last_name}",
                    'department' => $faculty->faculty_department,
                    'unit' => $faculty->faculty_unit,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Manual faculty attendance error: ' . $e->getMessage());

            return response()->json([
                'error' => 'An error occurred while recording attendance.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search all faculty (for manual attendance modal)
     */
    public function searchFaculty(Request $request)
    {
        $query = $request->get('query');

        if (empty($query) || strlen(trim($query)) < 2) {
            return response()->json([
                'faculty' => [],
                'message' => 'Query must be at least 2 characters long.'
            ]);
        }

        try {
            $faculty = DB::table('facultystaff')
                ->select(
                    'faculty_id as id',
                    'faculty_id as employee_id',
                    DB::raw("CONCAT(faculty_first_name, ' ', COALESCE(faculty_middle_initial, ''), '. ', faculty_last_name) as name"),
                    'faculty_department as department',
                    'faculty_unit as unit',
                    'faculty_email as email',
                    'faculty_phone_num as phone',
                    'faculty_profile_image as profile'
                )
                ->where(function($q) use ($query) {
                    $q->where(DB::raw("CONCAT(faculty_first_name, ' ', COALESCE(faculty_middle_initial, ''), '. ', faculty_last_name)"), 'LIKE', "%{$query}%")
                        ->orWhere('faculty_id', 'LIKE', "%{$query}%")
                        ->orWhere('faculty_department', 'LIKE', "%{$query}%")
                        ->orWhere('faculty_unit', 'LIKE', "%{$query}%");
                })
                ->orderBy('faculty_first_name')
                ->limit(15)
                ->get();

            return response()->json([
                'faculty' => $faculty,
                'count' => $faculty->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Faculty search error: ' . $e->getMessage());

            return response()->json([
                'faculty' => [],
                'message' => 'An error occurred while searching faculty.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check faculty attendance status - Fixed to match student logic
     */
    public function checkFacultyAttendance(Request $request)
    {
        $request->validate([
            'faculty_id' => 'required',
            'date' => 'required|date'
        ]);

        $facultyId = $request->input('faculty_id');
        $date = $request->input('date');

        try {
            // Get the most recent record for the faculty on the specified date
            // This is the key fix - order by created_at DESC and get the latest
            $record = DB::table('facultystaff_records')
                ->where('faculty_id', $facultyId)
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
                    'faculty_id' => $record->faculty_id,
                    'time_in' => $record->record_in,
                    'time_out' => $record->record_out,
                    'date' => $record->created_at,
                    'status' => $this->getAttendanceStatus($record->record_in, $record->record_out)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Check faculty attendance error: ' . $e->getMessage());
            Log::error('Faculty ID: ' . $facultyId . ', Date: ' . $date);

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
            return 'present'; // Faculty is currently inside
        } elseif ($hasTimeIn && $hasTimeOut) {
            return 'completed'; // Faculty has completed attendance
        } else {
            return 'no_record'; // No valid record
        }
    }

    public function facultyLog(Request $request)
    {
        $request->validate([
            'faculty_id' => 'required|exists:facultystaff,faculty_id',
        ]);

        $facultyId = $request->input('faculty_id');
        $now = Carbon::now('Asia/Manila');

        $faculty = Faculty::where('faculty_id', $facultyId)->first();

        if (!$faculty) {
            return response()->json(['error' => 'Faculty not found.'], 404);
        }

        // Get today's latest record
        $latestRecord = FacultyRecords::where('faculty_id', $facultyId)
            ->whereDate('created_at', $now->toDateString())
            ->latest()
            ->first();

        $status = '';

        if (!$latestRecord || $latestRecord->record_out !== null) {
            // Time In
            FacultyRecords::create([
                'faculty_id' => $facultyId,
                'record_in' => $now,
                'record_out' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $status = 'Time In';
            $this->logActivity(
                $request->user()->id ?? null,
                $request->user()->role ?? 'System',
                'Faculty/Staff Time In',
                "Faculty/Staff ID: {$facultyId}, Time-In By Guard ID:{$request->user()->id}"
            );
        } else {
            // Time Out
            $latestRecord->update([
                'record_out' => $now,
                'updated_at' => $now,
            ]);
            $status = 'Time Out';
            $this->logActivity(
                $request->user()->id ?? null,
                $request->user()->role ?? 'System',
                'Faculty/Staff Time Out',
                "Faculty/Staff ID: {$facultyId}, Time-Out By Guard ID:{$request->user()->id}"
            );
        }

        return response()->json([
            'status' => strtolower($status),
            'time' => $now->toDateTimeString(),
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
            Log::error('Failed to create activity log: ' . $e->getMessage());
        }
    }
}
