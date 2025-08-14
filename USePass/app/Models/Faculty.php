<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faculty extends Model
{
    use HasFactory;

    protected $table = 'facultyStaff';
    protected $primaryKey = 'faculty_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'faculty_id',
        'faculty_first_name',
        'faculty_last_name',
        'faculty_middle_initial',
        'faculty_gender',
        'faculty_college',
        'faculty_department',
        'faculty_unit',
        'faculty_email',
        'faculty_phone_num',
        'faculty_profile_image',
    ];

    public static function getCollegeDepartments()
    {
        return [
            'College of Teacher Education and Technology (CTET)' => [
                'Bachelor of Early Childhood Education Department',
                'Bachelor of Elementary Education Department',
                'Bachelor of Secondary Education - English Department',
                'Bachelor of Secondary Education - Filipino Department',
                'Bachelor of Secondary Education - Mathematics Department',
                'Bachelor of Science in Information Technology Department',
                'Bachelor of Special Needs Education Department',
                'Bachelor of Technical-Vocational Teacher Education Department',
                'General Education Department',
                'Graduate School - Doctor of Education Department',
                'Graduate School - Master of Education in Language Teaching Department',
                'Graduate School - Master of Education in Educational Management Department'
            ],
            'School of Medicine (SoM)' => [
                'Medical Faculty',
                'Administrative Staff',
                'Laboratory Services',
                'Support Staff'
            ],
            'College of Agriculture and Related Sciences (CARS)' => [
                'Agriculture Program',
                'Animal Science Program',
                'Crop Protection Program',
                'Environmental Science Program',
                'Food Science Program',
                'Forestry Program',
                'Horticulture Program',
                'Soil Science Program',
                'Agricultural Extension Program',
                'Administrative Staff',
                'Support Staff'
            ]
        ];
    }

    public static function getDepartmentsByCollege($college)
    {
        $departments = self::getCollegeDepartments();
        return $departments[$college] ?? [];
    }

    public static function getColleges()
    {
        return array_keys(self::getCollegeDepartments());
    }

    // Scope for filtering by college
    public function scopeByCollege($query, $college)
    {
        return $query->where('faculty_college', $college);
    }

    // Scope for filtering by department
    public function scopeByDepartment($query, $department)
    {
        return $query->where('faculty_department', $department);
    }

}
