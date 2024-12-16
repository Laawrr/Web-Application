<?php

namespace App\Http\Controllers;

use App\Models\FoundItem;
use Illuminate\Http\Request;

class FoundItemController extends Controller
{
    /**
     * Display a listing of the found items.
     */
    public function index()
    {
        $foundItems = FoundItem::all();
        return response()->json($foundItems);
    }

    /**
     * Store a newly created found item in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'found_date' => 'required|date',
            'facebook_link' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'image_url' => 'nullable|string|max:255',
            'user_id' => 'required|exists:users,id'
        ]);

        $foundItem = FoundItem::create($request->all());
        return response()->json($foundItem, 201);
    }

    /**
     * Display the specified found item.
     */
    public function show($id)
    {
        $foundItem = FoundItem::findOrFail($id);
        return response()->json($foundItem);
    }

    /**
     * Update the specified found item in storage.
     */
    public function update(Request $request, $id)
    {
        $foundItem = FoundItem::findOrFail($id);

        $request->validate([
            'item_name' => 'required|string|max:255',
            'found_date' => 'required|date',
            'facebook_link' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'image_url' => 'nullable|string|max:255',
            'user_id' => 'required|exists:users,id'
        ]);

        $foundItem->update($request->all());
        return response()->json($foundItem);
    }

    /**
     * Remove the specified found item from storage.
     */
    public function destroy($id)
    {
        $foundItem = FoundItem::findOrFail($id);
        $foundItem->delete();

        return response()->json(null, 204);
    }
}
