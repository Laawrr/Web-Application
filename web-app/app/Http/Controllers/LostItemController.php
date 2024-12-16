<?php

namespace App\Http\Controllers;

use App\Models\LostItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LostItemController extends Controller
{
    // Store method (existing code)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'lost_date' => 'required|date',
            'owner' => 'required|string|max:255',
            'facebook_link' => 'required|url',
            'contact_number' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('lost_items_images', 'public');
        }

        // Create a new LostItem
        $lostItem = new LostItem();
        $lostItem->name = $request->input('name');
        $lostItem->lost_date = $request->input('lost_date');
        $lostItem->owner = $request->input('owner');
        $lostItem->facebook_link = $request->input('facebook_link');
        $lostItem->contact_number = $request->input('contact_number');
        $lostItem->image_path = $imagePath;
        $lostItem->user_id = Auth::id();  // Set the user_id to the current authenticated user's ID
        $lostItem->save();

        return response()->json([
            'message' => 'Lost item successfully uploaded',
            'lost_item' => $lostItem,
        ], 201);
    }

    // Fetch all lost items
    public function index()
    {
        // Retrieve all lost items
        $lostItems = LostItem::all();

        // Return the items as a JSON response
        return response()->json($lostItems);
    }
}
