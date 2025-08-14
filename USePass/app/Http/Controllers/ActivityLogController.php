<?php
//ActivityLogController.php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    /**
     * Store a new activity log
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer',
            'role' => 'required|string|max:255',
            'log_action' => 'required|string|max:255',
            'log_description' => 'required|string',
        ]);

        try {
            $activityLog = ActivityLog::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Activity logged successfully',
                'data' => $activityLog
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all activity logs with pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $date = $request->get('date');

            $query = ActivityLog::with(['user' => function($query) {
                $query->select('id', 'first_name', 'last_name', 'email')
                    ->selectRaw("CONCAT(first_name, ' ', last_name) as name");
            }])->orderBy('created_at', 'desc');

            if ($date) {
                $query->whereDate('created_at', $date);
            }

            $logs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity logs by user
     */
    public function getByUser(Request $request, $userId): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $logs = ActivityLog::where('user_id', $userId)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user activity logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity logs by action type
     */
    public function getByAction(Request $request, $action): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $logs = ActivityLog::where('log_action', $action)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity logs by action',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
