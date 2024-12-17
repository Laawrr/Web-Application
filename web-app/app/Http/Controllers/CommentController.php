<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\LostItem;
use App\Models\FoundItem;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Store a comment
    public function store(Request $request)
    {
        // Ensure the user is authenticated
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the request
        $request->validate([
            'item_type' => 'required|string|in:lost,found',
            'item_id' => 'required|integer|exists:lost_items,id,found_items,id',  // Check if the item exists in either table
            'text' => 'required|string|max:500',
        ]);

        // Determine the model based on item_type
        $model = $request->item_type === 'lost' ? LostItem::class : FoundItem::class;

        // Find the item and create the comment
        $item = $model::findOrFail($request->item_id);
        $comment = $item->comments()->create([
            'user_id' => auth()->id(),  // Use authenticated user ID
            'text' => $request->text,
        ]);

        return response()->json(['success' => true, 'comment' => $comment], 201);
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
}
