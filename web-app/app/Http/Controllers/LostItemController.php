<?php

namespace App\Http\Controllers;

use App\Models\LostItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Import Storage for handling image paths

class LostItemController extends Controller
{
    /**
     * Display a listing of the lost items (with pagination).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Retrieve all lost items, paginated and ordered by latest lost date
        $lostItems = LostItem::orderBy('created_at', 'desc')->paginate(10); // 10 items per page
        
        return response()->json([
            'message' => 'Lost items retrieved successfully',
            'data' => $lostItems
        ]);
    }

    /**
     * Store a newly created lost item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'lost_date' => 'required|date',
            'owner' => 'required|string|max:255',
            'facebook_link' => 'required|url',
            'contact_number' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle image upload (if provided)
        $imagePath = null;
        if ($request->hasFile('image')) {
            try {
                // Store image and generate its public URL
                $imagePath = $request->file('image')->store('lost_items_images', 'public');
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to upload image',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        // Create a new LostItem record
        try {
            $lostItem = LostItem::create([
                'name' => $request->input('name'),
                'lost_date' => $request->input('lost_date'),
                'owner' => $request->input('owner'),
                'facebook_link' => $request->input('facebook_link'),
                'contact_number' => $request->input('contact_number'),
                'image_path' => $imagePath ? Storage::url($imagePath) : null, // Use URL for better usage on the front end
                'user_id' => Auth::id() // Associate the lost item with the logged-in user
            ]);

            return response()->json([
                'message' => 'Lost item successfully uploaded',
                'lost_item' => $lostItem
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create lost item',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
