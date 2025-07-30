<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getCounts()
    {
        $studentsCount = DB::table('students')->count();

        return response()->json([
            'students' => $studentsCount,
            'teachers' => 0,
            'visitors' => 0,
            'alumni' => 0,
        ]);
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
}
