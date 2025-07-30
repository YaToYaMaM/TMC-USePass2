<?php

namespace App\Http\Controllers;

use App\Models\IncidentReport;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function incident()
    {
        $user = auth()->user();

        // Admins see all, guards see only their own
        $reports = $user->role === 'admin'
            ? IncidentReport::orderBy('created_at', 'desc')->get()
            : IncidentReport::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return Inertia::render('Frontend/secIncident', [
            'reports' => $reports,
        ]);
    }
}

