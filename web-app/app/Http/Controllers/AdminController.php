<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\Claim;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth; // Make sure this is imported for authentication

class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin');
    }

    public function dashboard()
    {
        // Fetching the counts from the database
        $totalUsers = User::count();
        $lostItems = LostItem::count(); 
        $foundItems = FoundItem::count(); 
        $claims = Claim::count();

        // Return data in JSON format
        return response()->json([
            'totalUsers' => $totalUsers,
            'lostItems' => $lostItems,
            'foundItems' => $foundItems,
            'claims' => $claims,
        ]);
    }

    public function users()
    {
        return response()->json([
            'users' => User::all()
        ]);
    }

    public function usersLog()
    {
        $activityLog = ActivityLog::with('user:id,name')->get();
        // Add your user log retrieval logic here
        return response()->json([
            'activityLog' => $activityLog,
        ]);
    }

    public function reportedItems()
    {
        // Add your reported items retrieval logic here
        return response()->json([
            'items' => []
        ]);
    }

    // Add the getID method here for the authenticated admin user
    public function getID()
    {
        $user = Auth::user(); // Get the currently authenticated user (admin or regular user)

        return response()->json([
            'id' => $user->id, // Return the user's ID
            'last_login_at' => $user->last_login_at, // Return the user's last login time
        ]);
    }
}
