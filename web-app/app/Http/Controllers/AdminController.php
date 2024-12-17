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
}
