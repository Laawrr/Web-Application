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
        $request->validate([
            'item_type' => 'required|string|in:lost,found',
            'item_id' => 'required|integer',
            'text' => 'required|string|max:500',
        ]);

        // Determine the model
        $model = $request->item_type === 'lost' ? LostItem::class : FoundItem::class;

        // Find the item and create a comment
        $item = $model::findOrFail($request->item_id);
        $comment = $item->comments()->create([
            'user_id' => auth()->id(),
            'text' => $request->text,
        ]);

        return response()->json(['success' => true, 'comment' => $comment]);
    }

    // Fetch comments
    public function index($item_type, $item_id)
    {
        $model = $item_type === 'lost' ? LostItem::class : FoundItem::class;
        $item = $model::findOrFail($item_id);

        $comments = $item->comments()->with('user')->get();

        return response()->json(['comments' => $comments]);
    }
}
