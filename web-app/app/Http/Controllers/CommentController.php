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

            // Find the item and create the comment
            $item = $model::findOrFail($request->item_id);

            // Add logging to verify item found
            Log::info('Item found', ['item' => $item]);

            $comment = $item->comments()->create([
                'user_id' => auth()->id(),
                'text' => $request->text,
            ]);

            return response()->json(['success' => true, 'comment' => $comment], 201);
        } catch (\Exception $e) {
            Log::error('Error in storing comment', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    // Fetch comments for a specific item
    public function index($item_type, $item_id)
    {
        // Determine the model based on item_type
        $model = $item_type === 'lost' ? LostItem::class : FoundItem::class;

        // Find the item
        $item = $model::findOrFail($item_id);

        // Fetch comments and include user information
        $comments = $item->comments()->with('user:id,name')->get();

        return response()->json(['comments' => $comments]);
    }

    public function view()
    {
        try {
            // Fetch all comments from both LostItem and FoundItem models
            $lostItemComments = LostItem::with('comments.user:id,name')->get()->pluck('comments')->flatten();
            $foundItemComments = FoundItem::with('comments.user:id,name')->get()->pluck('comments')->flatten();
    
            // Merge the comments from both models
            $allComments = $lostItemComments->merge($foundItemComments);
    
            // Return the comments as a JSON response
            return response()->json(['comments' => $allComments]);
        } catch (\Exception $e) {
            Log::error('Error in fetching all comments', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
