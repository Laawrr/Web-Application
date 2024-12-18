<?php

// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\LostItem;
use App\Models\FoundItem;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::orderBy('created_at', 'desc')->get();

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::find($id);
        if ($notification) {
            // Check if the notification is for a lost item
            $lostItem = LostItem::find($notification->notifiable_id);
            if ($lostItem && $lostItem->user_id == $request->user()->id) {
                $notification->read_at = now();
                $notification->save();
                return response()->json(['message' => 'Notification marked as read']);
            }

            // Check if the notification is for a found item
            $foundItem = FoundItem::find($notification->notifiable_id);
            if ($foundItem && $foundItem->user_id == $request->user()->id) {
                $notification->read_at = now();
                $notification->save();
                return response()->json(['message' => 'Notification marked as read']);
            }

            return response()->json(['message' => 'Notification not related to any of your items'], 403);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }

    // Store a new notification
    public function store(Request $request)
    {
        $notification = new Notification();
        $notification->user_id = $request->user()->id;
        $notification->type = $request->type;  
        $notification->data = $request->data;  
        $notification->notifiable_type = $request->notifiable_type;  
        $notification->notifiable_id = $request->notifiable_id;  
        $notification->save();

        return response()->json(['message' => 'Notification created successfully!']);
    }
}
