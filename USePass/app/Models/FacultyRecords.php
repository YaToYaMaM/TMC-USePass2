<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacultyRecords extends Model
{
    use HasFactory;
    protected $table = 'facultystaff_records';
    protected $primaryKey = 'record_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'faculty_id',
        'record_in',
        'record_out',
    ];
    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'faculty_id');
    }
}
