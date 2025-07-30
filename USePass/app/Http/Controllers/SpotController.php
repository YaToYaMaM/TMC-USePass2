<?php

namespace App\Http\Controllers;

use App\Models\SpotReport;
use Inertia\Inertia;

class SpotController extends Controller
{
    public function spot()
    {
        $user = auth()->user();

        $reports = $user->role === 'admin'
            ? SpotReport::orderBy('created_at', 'desc')->get()
            : SpotReport::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return Inertia::render('Frontend/secIncident', [
            'reports' => $reports,
            'auth' => [
                'user' => $user
            ]
        ]);
    }
}
