<?php

namespace App\Http\Controllers;

use App\Models\LostItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LostItemController extends Controller
{
    /**
     * Store a newly created lost item in storage.
     */
    public function store(Request $request)
    {
        // Validate other fields
        $request->validate([
            'lost_date' => 'required|date',
            'facebook_link' => 'required|url',
            'contact_number' => 'required|string',
            'description' => 'required|string',
            'category' => 'required|string',
            'location' => 'required|string',
            'image_url' => 'nullable|string',
        ]);

        // Retrieve user_id from session
        $userId = Session::get('user_id'); // Ensure this is set when the user logs in

        if ($userId) {
            $lostItem = LostItem::create([
                'user_id' => $userId,
                'lost_date' => $request->lost_date,
                'facebook_link' => $request->facebook_link,
                'contact_number' => $request->contact_number,
                'description' => $request->description,
                'category' => $request->category,
                'location' => $request->location,
                'image_url' => $request->image_url,
            ]);

            return response()->json(['message' => 'Lost item saved successfully', 'data' => $lostItem], 201);
        }

        return response()->json(['error' => 'User not logged in or invalid session'], 401);
    }

    /**
     * Fetch all lost items.
     */
    public function index()
    {
        $lostItems = LostItem::all();
        return response()->json($lostItems);
    }

    /**
     * Display the specified lost item.
     */
    public function show($id)
    {
        $lostItem = LostItem::findOrFail($id);  // Retrieve the lost item by its ID
        return response()->json($lostItem);
    }

    /**
     * Update the specified lost item in storage.
     */
    public function update(Request $request, $id)
    {
        $lostItem = LostItem::findOrFail($id);

        $request->validate([
            'lost_date' => 'required|date',
            'facebook_link' => 'required|url',
            'contact_number' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Update attributes
        $lostItem->lost_date = $request->input('lost_date');
        $lostItem->facebook_link = $request->input('facebook_link');
        $lostItem->contact_number = $request->input('contact_number');
        $lostItem->description = $request->input('description');
        $lostItem->category = $request->input('category');
        $lostItem->location = $request->input('location');

        // Handle image update (if exists)
        if ($request->hasFile('image_url')) {
            // Delete old image if it exists
            if ($lostItem->image_url) {
                Storage::disk('public')->delete($lostItem->image_url);
            }
            // Store the new image
            $lostItem->image_url = $request->file('image_url')->store('lost_items_images', 'public');
        }

        $lostItem->save();

        return response()->json([
            'message' => 'Lost item successfully updated',
            'lost_item' => $lostItem,
        ], 200);
    }

    /**
     * Remove the specified lost item from storage.
     */
    public function destroy($id)
    {
        $lostItem = LostItem::findOrFail($id);
        
        // Delete associated image
        if ($lostItem->image_url) {
            Storage::disk('public')->delete($lostItem->image_url);
        }

        // Delete the lost item
        $lostItem->delete();

        return response()->json([
            'message' => 'Lost item successfully deleted',
        ], 204);
    }
}
