<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\Log;

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

        $records = $query->get();

        return response()->json($records);
    }

    public function index(Request $request)
    {
        $date = $request->query('date');

        if (empty($date)) {
            return response()->json([
                'message' => 'Date parameter is required.'
            ], 400);
        }

        $records = StudentRecord::whereDate('record_in', $date)->get(); // ✅ Eloquent usage

        return response()->json($records);
    }
}
