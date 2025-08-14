<?php

namespace App\Http\Controllers;
use App\Models\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\ActivityLog;


class GuardController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'last_name' => 'required|string',
            'first_name' => 'required|string',
            'middle_initial' => 'nullable|string|max:1',
            'contact_number' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'profile_image' => 'nullable|image|max:5120',
        ]);
        $imagePath = null;
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $destinationPath = public_path('guard_profiles');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);
            $imagePath = 'guard_profiles/' . $imageName;
        }

        $guard = Guard::create([
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
            'middle_initial' => $request->middle_initial,
            'role' => 'guard',
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_image' => $imagePath,
        ]);

        // Log the activity after successful creation
        $this->logActivity(
            $request->user()->id ?? null, // Assuming you have authenticated user, use null if not
            $request->user()->role ?? 'System', // Get user role or default to 'System'
            'Guard Created',
            "New Guard Added: {$guard->first_name} {$guard->last_name}"
        );
        return response()->json(['message' => 'Guard created successfully.']);
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

    public function index()
    {
        $guards = Guard::where('role', 'guard')->get();
        return response()->json($guards);
    }
    public function update(Request $request, $id)
    {
        $guard = Guard::where('role', 'guard')->findOrFail($id);

        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'contact_number' => 'required|string',
        ]);

        $guard->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'contact_number' => $request->contact_number,
        ]);

        // Log the activity after successful creation
        $this->logActivity(
            $request->user()->id ?? null, // Assuming you have authenticated user, use null if not
            $request->user()->role ?? 'System', // Get user role or default to 'System'
            'Guard Updated',
            "Guard Updated Information: {$guard->first_name} {$guard->last_name}"
        );

        return response()->json(['message' => 'Guard updated successfully.']);
    }





}
