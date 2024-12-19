<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\LostItem;
use App\Models\FoundItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Ensure the user is authenticated
            if (!auth()->check()) {
                Log::warning('Unauthorized comment attempt');
                return response()->json(['error' => 'Unauthorized'], 401);
            }
    
            Log::info('Comment request data:', $request->all());
            Log::info('Authenticated user:', ['user_id' => auth()->id(), 'user' => auth()->user()]);
    
            // Validate the request
            $validatedData = $request->validate([
                'item_type' => 'required|string|in:lost,found',
                'item_id' => ['required', 'integer', function ($attribute, $value, $fail) use ($request) {
                    $model = $request->item_type === 'lost' ? LostItem::class : FoundItem::class;
                    if (!$model::find($value)) {
                        $fail('The selected item does not exist.');
                    }
                }],
                'text' => 'required|string|max:500',
            ]);
    
            Log::info('Request validated successfully', ['data' => $validatedData]);
    
            // Determine the model based on item_type
            $modelClass = $request->item_type === 'lost' ? LostItem::class : FoundItem::class;
    
            // Find the item
            $item = $modelClass::findOrFail($request->item_id);
            Log::info('Found item:', ['item' => $item->toArray()]);
    
            DB::beginTransaction();
            try {
                // Debug log before comment creation
                Log::info('Attempting to create comment with data:', [
                    'user_id' => auth()->id(),
                    'text' => $request->text,
                    'commentable_id' => $item->id,
                    'commentable_type' => get_class($item)
                ]);

                // Create the comment
                $comment = new Comment();
                $comment->fill([
                    'user_id' => auth()->id(),
                    'text' => $request->text,
                    'commentable_id' => $item->id,
                    'commentable_type' => get_class($item)
                ]);

                // Debug log comment object before saving
                Log::info('Comment object before save:', $comment->toArray());

                $comment->save();
                
                // Load the user relationship for the response
                $comment->load('user');
                
                Log::info('Comment created successfully:', ['comment' => $comment->toArray()]);
    
                // Create notification for the item owner
                if ($item->user_id !== auth()->id()) {
                    try {
                        $notificationData = [
                            'item_id' => $item->id,
                            'item_type' => $request->item_type,
                            'comment_id' => $comment->id,
                            'item_name' => $item->name ?? $item->item_name ?? 'Unknown Item',
                            'commenter_name' => auth()->user()->name
                        ];

                        Log::info('Creating notification with data:', [
                            'notification_data' => $notificationData,
                            'user_id' => $item->user_id
                        ]);

                        $notification = Notification::create([
                            'user_id' => $item->user_id,
                            'title' => auth()->user()->name . ' commented on your item',
                            'message' => auth()->user()->name . ' commented on your ' . $request->item_type . ' item',
                            'type' => 'comment',
                            'read' => false,
                            'data' => $notificationData
                        ]);

                        Log::info('Notification created successfully:', ['notification' => $notification->toArray()]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create notification:', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        // Don't throw the error - we still want to return the comment
                    }
                }
    
                DB::commit();
    
                return response()->json([
                    'message' => 'Comment added successfully',
                    'comment' => $comment
                ]);
    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error creating comment:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Failed to add comment',
                    'message' => $e->getMessage(),
                    'details' => $e->getTraceAsString()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in comment submission:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to add comment',
                'message' => $e->getMessage(),
                'details' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $comment = Comment::with('user')->findOrFail($id);
            return response()->json($comment);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Comment not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $comment = Comment::findOrFail($id);

            // Check if the user owns the comment
            if ($comment->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validatedData = $request->validate([
                'text' => 'required|string|max:500',
            ]);

            $comment->update($validatedData);
            return response()->json(['message' => 'Comment updated successfully', 'comment' => $comment]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update comment'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $comment = Comment::findOrFail($id);

            // Check if the user owns the comment
            if ($comment->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $comment->delete();
            return response()->json(['message' => 'Comment deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete comment'], 500);
        }
    }

    // Fetch comments for a specific item with pagination
    public function index($item_type, $item_id)
    {
        try {
            Log::info('Fetching comments', [
                'item_type' => $item_type,
                'item_id' => $item_id
            ]);

            // Determine the model based on item_type
            $model = $item_type === 'lost' ? LostItem::class : FoundItem::class;

            // Find the item
            $item = $model::findOrFail($item_id);

            // Fetch comments with pagination and eager load user relationship
            $comments = $item->comments()
                ->with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'text' => $comment->text,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name
                        ],
                        'created_at' => $comment->created_at,
                        'updated_at' => $comment->updated_at
                    ];
                });

            Log::info('Comments fetched successfully', [
                'count' => $comments->count(),
                'first_comment' => $comments->first()
            ]);

            return response()->json([
                'success' => true,
                'comments' => $comments
            ]);
        } catch (\Exception $e) {
            Log::error('Error in fetching comments for item', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Fetch all comments from LostItem and FoundItem models with pagination
    public function view()
    {
        try {
            // Fetch comments with pagination for both LostItem and FoundItem models
            $lostItemComments = LostItem::with('comments.user:id,name')->get()->pluck('comments')->flatten();
            $foundItemComments = FoundItem::with('comments.user:id,name')->get()->pluck('comments')->flatten();

            // Merge the comments from both models
            $allComments = $lostItemComments->merge($foundItemComments)->sortByDesc('created_at');

            // Return the comments as a JSON response (could add pagination here)
            return response()->json(['comments' => $allComments->values()->all()]);
        } catch (\Exception $e) {
            Log::error('Error in fetching all comments', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
