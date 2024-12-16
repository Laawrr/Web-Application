<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Log user activity.
     *
     * @param  string  $action
     * @return void
     */
    public function logUserActivity($action)
    {
        // Check if user is authenticated
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(), // Get the current logged-in user's ID
                'action' => $action, // The action that the user performed
                'action_time' => now(), // Current timestamp for action time
                'ip_address' => request()->ip(), // Get the user's IP address
                'user_agent' => request()->header('User-Agent'), // Get the user's browser/OS
            ]);
        }
    }
}
