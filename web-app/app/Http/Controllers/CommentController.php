<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\LostItem;
use App\Models\FoundItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;

class CommentController extends Controller
{
    // Store a comment
    public function store(Request $request)
    {
        try {
            // Ensure the user is authenticated
            if (!auth()->check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
    
            // Validate the request
            $request->validate([
                'item_type' => 'required|string|in:lost,found',
                'item_id' => ['required', 'integer', function ($attribute, $value, $fail) {
                    if (!LostItem::find($value) && !FoundItem::find($value)) {
                        $fail('The selected item does not exist in either Lost or Found items.');
                    }
                }],
                'text' => 'required|string|max:500',
            ]);
    
            Log::info('Request validated successfully', ['data' => $request->all()]);
    
            // Determine the model based on item_type
            $model = $request->item_type === 'lost' ? LostItem::class : FoundItem::class;
    
            // Find the item
            $item = $model::findOrFail($request->item_id);
    
            // Add logging to verify item found
            Log::info('Item found', ['item' => $item]);

            // Create the comment with proper polymorphic relationship
            $comment = new Comment([
                'user_id' => auth()->id(),
                'text' => $request->text
            ]);
            
            // Associate the comment with the item
            $item->comments()->save($comment);

            // Load the user relationship for the response
            $comment->load('user:id,name');

            // Create notification for the item owner
            if ($item->user_id !== auth()->id()) {
                Notification::create([
                    'type' => 'new_comment',
                    'user_id' => $item->user_id,
                    'data' => [
                        'item_type' => $request->item_type,
                        'item_id' => $item->id,
                        'item_name' => $item->item_name,
                        'commenter_name' => auth()->user()->name,
                        'comment_text' => $request->text
                    ]
                ]);
            }

            return response()->json(['success' => true, 'comment' => $comment], 201);
        } catch (\Exception $e) {
            Log::error('Error in storing comment', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal Server Error', 'message' => $e->getMessage()], 500);
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
