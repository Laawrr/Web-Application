<?php

// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                // Parse the data to get user information
                $data = json_decode($notification->data, true);
                
                // Get the comment author's name if this is a comment notification
                $userName = 'Unknown User';
                if ($notification->type === 'comment') {
                    if (isset($data['commenter_name'])) {
                        $userName = $data['commenter_name'];
                    } elseif (isset($data['comment_id'])) {
                        $comment = \App\Models\Comment::with('user')->find($data['comment_id']);
                        if ($comment && $comment->user) {
                            $userName = $comment->user->name;
                            // Update the notification data to include the commenter name
                            $data['commenter_name'] = $userName;
                            $notification->data = json_encode($data);
                            $notification->save();
                        }
                    }
                }

                // Format the date
                $createdAt = $notification->created_at ? $notification->created_at->format('M d, Y, h:i A') : null;

                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $data, // Return parsed data instead of JSON string
                    'read_at' => $notification->read_at,
                    'created_at' => $createdAt,
                    'userName' => $userName
                ];
            });

        return response()->json(['notifications' => $notifications]);
    }

    public function markAsRead(Request $request, $id)
    {
        try {
            $notification = Notification::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }

            if (!$notification->read_at) {
                $notification->read_at = now();
                $notification->save();
            }

            return response()->json([
                'message' => 'Notification marked as read',
                'notification' => [
                    'id' => $notification->id,
                    'read_at' => $notification->read_at
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking notification as read:', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to mark notification as read'], 500);
        }
    }

    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function getUnreadCount(Request $request)
    {
        $count = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
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
