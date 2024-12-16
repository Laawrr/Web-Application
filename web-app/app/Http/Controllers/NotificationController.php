<?php

// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Fetch notifications for a specific user
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    // Mark a notification as read
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($notification) {
            $notification->read_at = now();
            $notification->save();
            return response()->json(['message' => 'Notification marked as read']);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }

    // Store a new notification
    public function store(Request $request)
    {
        // Example: Create a notification for a user
        $notification = new Notification();
        $notification->user_id = $request->user()->id;
        $notification->type = $request->type;  // Notification type (e.g., 'new_message')
        $notification->data = $request->data;  // Notification data (could be a message, or any extra info)
        $notification->notifiable_type = $request->notifiable_type;  // Model that the notification relates to (e.g., 'App\Models\Post')
        $notification->notifiable_id = $request->notifiable_id;  // ID of the related model
        $notification->save();

        return response()->json(['message' => 'Notification created successfully!']);
    }
}
