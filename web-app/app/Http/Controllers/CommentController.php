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
                // Create the comment
                $comment = new Comment([
                    'user_id' => auth()->id(),
                    'text' => $request->text,
                ]);

                // Associate with the polymorphic relationship
                $comment->commentable_id = $item->id;
                $comment->commentable_type = $modelClass;
                $comment->save();
                
                // Load the user relationship for the response
                $comment->load('user');
                
                Log::info('Comment created:', ['comment' => $comment->toArray()]);
    
                // Create notification for the item owner
                if ($item->user_id !== auth()->id()) {
                    $notificationData = [
                        'item_id' => $item->id,
                        'item_type' => $request->item_type,
                        'comment_id' => $comment->id,
                        'item_name' => $item->name ?? $item->item_name ?? 'Unknown Item',
                        'commenter_name' => auth()->user()->name
                    ];

                    Notification::create([
                        'user_id' => $item->user_id,
                        'title' => 'New Comment',
                        'message' => auth()->user()->name . ' commented on your ' . $request->item_type . ' item.',
                        'type' => 'comment',
                        'read' => false,
                        'data' => json_encode($notificationData)
                    ]);
                }
    
                DB::commit();
    
                return response()->json([
                    'message' => 'Comment added successfully',
                    'comment' => $comment
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Database transaction failed:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed:', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error in CommentController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
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
