<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\IncidentReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function incident(Request $request)
    {
        $user = auth()->user();

        // Admins see all, guards see only their own
        $reports = $user->role === 'admin'
            ? IncidentReport::with('user')->orderBy('created_at', 'desc')->get()
            : IncidentReport::with('user')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        // Since guard_name is now stored in database, we don't need to map it
        // But keep this for backward compatibility with existing records
        $reports = $reports->map(function ($report) {
            // Use stored guard_name if available, otherwise fallback to user relationship
            if (empty($report->guard_name) && $report->user) {
                $report->guard_name = $report->user->first_name . ' ' . $report->user->last_name;
            } elseif (empty($report->guard_name)) {
                $report->guard_name = 'Unknown Guard';
            }
            return $report;
        });

        // Check if this is an AJAX/API request
        if ($request->expectsJson()) {
            return response()->json($reports);
        }

        // Render different views based on user role
        if ($user->role === 'admin') {
            return Inertia::render('Frontend/secIncident', [
                'reports' => $reports,
            ]);
        } else {
            return Inertia::render('Frontend/guardHome', [
                'reports' => $reports,
            ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'what' => 'required|string',
            'who' => 'required|string',
            'where' => 'required|string',
            'when' => 'required|string',
            'how' => 'required|string',
            'why' => 'required|string',
            'recommendation' => 'required|string',
            'incidentPicture.*' => 'nullable|image|max:5120',
        ]);

        $paths = [];

        if ($request->hasFile('incidentPicture')) {
            foreach ($request->file('incidentPicture') as $file) {
                $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('incident_pictures'), $filename);
                $paths[] = 'incident_pictures/' . $filename;
            }
        }

        $user = auth()->user();

        $report = new IncidentReport();
        $report->fill($validated);
        $report->user_id = $user->id;
        $report->guard_name = $user->first_name . ' ' . $user->last_name; // Store guard name
        $report->incidentPicture = $paths; // This will be automatically cast to JSON
        $report->save();

        $this->logActivity(
            $request->user()->id ?? null, // Assuming you have authenticated user, use null if not
            $request->user()->role ?? 'System', // Get user role or default to 'System'
            'Incident Report Created',
            "Incident Report Added: {$report->guard_name} {$report->what}"
        );

        return back()->with('success', 'Report submitted.');
    }

    public function print(Request $request)
    {
        $reportData = $request->only([
            'id', 'name', 'date', 'what', 'who',
            'where', 'when', 'how', 'why', 'recommendation'
        ]);

        $this->logActivity(
            $request->user()->id ?? null, // Assuming you have authenticated user, use null if not
            $request->user()->role ?? 'System', // Get user role or default to 'System'
            'Incident Report Print',
            "Incident Report Printed: {$reportData->guard_name} {$reportData->what}"
        );

        return view('reports.print', compact('reportData'));
    }

    private function logActivity($userId, $role, $action, $description)
    {
        try {
            ActivityLog::create([
                'user_id' => $userId,
                'role' => $role,
                'log_action' => $action,
                'log_description' => $description,
            ]);
        } catch (\Exception $e) {
            // Log to Laravel's default log if activity logging fails
            \Log::error('Failed to create activity log: ' . $e->getMessage());
        }
    }
}
