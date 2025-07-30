<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ParentCredential extends Model
{
    protected $table = 'parents';
    protected $primaryKey = 'parent_id'; // This is auto-increment integer
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'parent_last_name',
        'parent_first_name',
        'parent_middle_initial',
        'parent_phone_num',
        'parent_email',
        'parent_relation',
        'students_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'students_id', 'students_id');
    }
}
