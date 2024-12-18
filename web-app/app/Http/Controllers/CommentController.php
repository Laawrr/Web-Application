<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\LostItem;
use App\Models\FoundItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
    
            // Create the comment within the try-catch block
            try {
                $comment = $item->comments()->create([
                    'user_id' => auth()->id(),
                    'text' => $request->text,
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating comment', ['exception' => $e->getMessage(), 'data' => $request->all()]);
                return response()->json(['error' => 'Failed to store comment'], 500);
            }
    
            return response()->json(['success' => true, 'comment' => $comment], 201);
        } catch (\Exception $e) {
            Log::error('Error in storing comment', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
    
    // Fetch comments for a specific item with pagination
    public function index($item_type, $item_id)
    {
        try {
            // Determine the model based on item_type
            $model = $item_type === 'lost' ? LostItem::class : FoundItem::class;

            // Find the item
            $item = $model::findOrFail($item_id);

            // Fetch comments with pagination, include user information
            $comments = $item->comments()->with('user:id,name')->paginate(10);

            return response()->json(['comments' => $comments]);
        } catch (\Exception $e) {
            Log::error('Error in fetching comments for item', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
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
