<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\IncidentReport;
use App\Models\SpotReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class SpotController extends Controller
{
    public function spot(Request $request)
    {
        $user = auth()->user();

        // Admins see all, guards see only their own
        $reports = $user->role === 'admin'
            ? SpotReport::with('user')->orderBy('created_at', 'desc')->get()
            : SpotReport::with('user')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        // Transform data to match frontend expectations
        $reports = $reports->map(function ($report) {
            // Use the guard_name from database if it exists, otherwise use user relationship
            $guardName = $report->guard_name ?: ($report->user ?
                $report->user->first_name . ' ' . $report->user->last_name :
                'Unknown Guard');

            return [
                'id' => $report->id,
                'user_id' => $report->user_id,
                'findings' => $report->findings,
                'team_leader' => $report->team_leader,
                'guard_name' => $guardName,
                'action_taken' => $report->action_taken,
                'department_representative' => $report->department_representative,
                'location' => $report->location,
                'time' => $report->time,
                'date' => $report->date,
                'is_printed' => $report->is_printed,
                'printed_at' => $report->printed_at,
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
                'spotPicture' => $report->spotPicture,
                // Add aliases for frontend compatibility
                'guardName' => $guardName,
                'teamLeader' => $report->team_leader,
                'actionTaken' => $report->action_taken,
                'departmentRepresentative' => $report->department_representative,
                'Rtype' => 'Spot Report'
            ];
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
        // Validate the incoming request
        $validated = $request->validate([
            'findings' => 'required|string',
            'team_leader' => 'required|string|max:255',
            'guard_name' => 'required|string|max:255',
            'action_taken' => 'required|string',
            'department_representative' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'time' => 'required|string|max:255',
            'date' => 'required|date',
            'spotPicture.*' => 'nullable|image|mimes:jpeg,jpg,png|max:5120', // 5MB max per file
        ]);

        $paths = [];

        // Handle file uploads
        if ($request->hasFile('spotPicture')) {
            foreach ($request->file('spotPicture') as $file) {
                $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('spot_pictures'), $filename);
                $paths[] = 'spot_pictures/' . $filename;
            }
        }

        // Create the spot report
        $report = SpotReport::create([
            'user_id' => auth()->id(),
            'findings' => $validated['findings'],
            'team_leader' => $validated['team_leader'],
            'guard_name' => $validated['guard_name'],
            'action_taken' => $validated['action_taken'],
            'department_representative' => $validated['department_representative'],
            'location' => $validated['location'],
            'time' => $validated['time'],
            'date' => $validated['date'],
            'spotPicture' => $paths, // This will be cast to JSON
            'is_printed' => false,
        ]);

        // Get updated reports for the response
        $user = auth()->user();
        $reports = $user->role === 'admin'
            ? SpotReport::with('user')->orderBy('created_at', 'desc')->get()
            : SpotReport::with('user')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        // Transform data to match frontend expectations
        $reports = $reports->map(function ($report) {
            $guardName = $report->guard_name ?: ($report->user ?
                $report->user->first_name . ' ' . $report->user->last_name :
                'Unknown Guard');

            return [
                'id' => $report->id,
                'user_id' => $report->user_id,
                'findings' => $report->findings,
                'team_leader' => $report->team_leader,
                'guard_name' => $guardName,
                'action_taken' => $report->action_taken,
                'department_representative' => $report->department_representative,
                'location' => $report->location,
                'time' => $report->time,
                'date' => $report->date,
                'is_printed' => $report->is_printed,
                'printed_at' => $report->printed_at,
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
                'spotPicture' => $report->spotPicture,
                // Add aliases for frontend compatibility
                'guardName' => $guardName,
                'teamLeader' => $report->team_leader,
                'actionTaken' => $report->action_taken,
                'departmentRepresentative' => $report->department_representative,
                'Rtype' => 'Spot Report'
            ];
        });

        $this->logActivity(
            $request->user()->id ?? null, // Assuming you have authenticated user, use null if not
            $request->user()->role ?? 'System', // Get user role or default to 'System'
            'Spot Report Created',
            "Spot Report Added: {$report->guard_name} {$report->findings}"
        );

        // Redirect back with the updated reports data
        return redirect()->back()->with([
            'success' => 'Spot report created successfully!',
            'reports' => $reports
        ]);
    }

    public function print(Request $request)
    {
        $reportData = $request->only([
            'id', 'findings', 'team_leader', 'guard_name', 'action_taken',
            'department_representative', 'location', 'time', 'date'
        ]);

        $this->logActivity(
            $request->user()->id ?? null, // Assuming you have authenticated user, use null if not
            $request->user()->role ?? 'System', // Get user role or default to 'System'
            'Spot Report Print',
            "Spot Report Printed: {$reportData->guard_name} {$reportData->findings}"
        );

        return view('reports.spot-print', compact('reportData'));
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
