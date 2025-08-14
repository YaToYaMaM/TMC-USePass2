<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class StudentsImport implements ToModel, WithHeadingRow
{

    public function model(array $row)
    {
        return new Student([
            'students_id'         => $row['students_id'],
            'students_first_name' => $row['students_first_name'],
            'students_last_name'  => $row['students_last_name'],
            'students_middle_initial' => $row['students_middle_initial'],
            'students_gender'     => $row['students_gender'],
            'students_program'    => $row['students_program'],
            'students_major'      => $row['students_major'],
            'students_unit'       => $row['students_unit'],
        ]);


    }
}
