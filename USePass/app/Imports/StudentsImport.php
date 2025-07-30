<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel
{

    public function model(array $row)
    {
        return new Student([
            'students_last_name'       => $row['last_name'],
            'students_first_name'      => $row['first_name'],
            'students_middle_initial'  => $row['middle_initial'],
            'students_gender'          => $row['gender'],
            'students_id'              => $row['id_number'],
            'students_program'         => $row['program'],
            'students_major'           => $row['major'],
            'students_unit'            => $row['unit'],
            'students_email'           => $row['email'],
            'students_phone_num'       => $row['contact_number'],
        ]);
    }
}
