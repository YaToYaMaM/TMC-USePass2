<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRecord extends Model
{
    use HasFactory;

    // Explicitly define the table name if it doesn't follow Laravel's naming convention
    protected $table = 'students_records';

    // Optional: Define the primary key if it's not 'id'
    protected $primaryKey = 'record_id';

    // Optional: If your primary key is not auto-incrementing
    public $incrementing = true;

    // Optional: If your primary key is not an integer
    protected $keyType = 'int';

    // If you want timestamps (created_at, updated_at) managed automatically
    public $timestamps = true;

    // Define fillable fields if you want mass assignment
    protected $fillable = [
        'students_id',
        'record_in',
        'record_out',
    ];

    // Define relationships (optional)
    public function student()
    {
        return $this->belongsTo(Student::class, 'students_id');
    }

}
