<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\Claim;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth; // Import for authentication
use Illuminate\Support\Facades\Hash; // Import for password hashing
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin');
    }

    public function dashboard()
    {
        $totalUsers = User::count();
        $lostItems = LostItem::count();
        $foundItems = FoundItem::count();
        $claims = Claim::count();

        return response()->json([
            'totalUsers' => $totalUsers,
            'lostItems' => $lostItems,
            'foundItems' => $foundItems,
            'claims' => $claims,
        ]);
    }

    public function users()
    {
        $users = User::all();

        return response()->json([
            'users' => $users,
        ]);
    }

    public function usersLog()
    {
        $activityLog = ActivityLog::with('user:id,name')->get();

        return response()->json([
            'activityLog' => $activityLog,
        ]);
    }

    public function reportedItems()
    {
        $lostItems = LostItem::with('user:id,name', 'claim:claim_id,claim_status')->get();
        $foundItems = FoundItem::with('user:id,name', 'claim:claim_id,claim_status')->get();

        return response()->json([
            'lostItems' => $lostItems,
            'foundItems' => $foundItems,
        ]);
    }

    public function getID()
    {
        $user = Auth::user();

        return response()->json([
            'id' => $user->id,
            'last_login_at' => $user->last_login_at,
        ]);
    }

    public function store(Request $request)
    {
        // Validate Request Data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,user',
        ]);
    
        try {
            // Create New User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);
    
            // Log the action after user creation
            if (Auth::check()) {
                $action = 'User created: ' . $user->name;
                ActivityLog::create([
                    'user_id' => Auth::id(),  // This will log the current authenticated user's ID
                    'action' => $action,
                    'action_time' => now(),
                    'ip_address' => request()->getClientIp(),
                    'user_agent' => request()->header('User-Agent'),
                ]);
            }
    
            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            Log::error('User Creation Failed: ' . $e->getMessage());
    
            return response()->json([
                'error' => 'Failed to create user. Please try again.',
            ], 500);
        }
    }
}
