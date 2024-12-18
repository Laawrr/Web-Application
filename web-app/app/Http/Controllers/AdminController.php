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

    // New method to add a user
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:Admin,Editor,Viewer', // Valid roles
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash the password
            'role' => $request->role,
        ]);

        // Log the action
        ActivityLog::create([
            'user_id' => Auth::id(), // Current admin user ID
            'action' => 'Created a new user: ' . $user->name,
            'action_time' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        // Return the newly created user in the response
        return response()->json($user, 201);
    }
}
