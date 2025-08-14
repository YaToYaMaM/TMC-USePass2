<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    use HasFactory;

    protected $primaryKey = 'incident_id';

    protected $fillable = [
        'user_id',
        'guard_name',
        'description',
        'type',
        'date',
        'what',
        'who',
        'where',
        'when',
        'how',
        'why',
        'recommendation',
        'incidentPicture',
    ];

    protected $casts = [
        'date' => 'date',
        'incidentPicture' => 'array',
    ];

    /**
     * Get the user that owns the incident report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
