<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guard extends Model
{
    use HasFactory;
    protected $table = 'users';
    protected $fillable = [
        'last_name',
        'first_name',
        'middle_initial',
        'role',
        'contact_number',
        'profile_image',
        'email',
        'password',
    ];
}
