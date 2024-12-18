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
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(), 
                'action' => $action, 
                'action_time' => now(), 
                'ip_address' => request()->getClientIp(),
                'user_agent' => request()->header('User-Agent'), 
            ]);
        }
    }
}
