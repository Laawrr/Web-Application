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
     * Show the form for creating a new found item.
     */
    public function create()
    {
        return response()->json('Create found item form.');
    }

    /**
     * Store a newly created found item in storage.
     */
    public function store(Request $request)
    {
        $imageUrl = null;
        if ($request->hasFile('image_url')) {
            $image = $request->file('image_url');
            $filename = time() . '.' . $image->extension();
            $image->move(public_path('assets/img'), $filename);
            $imageUrl = 'assets/img/' . $filename;
        }

        $request->validate([
            'found_date' => 'required|date',
            'item_name' => 'required|string|max:255',
            'facebook_link' => 'nullable|url',
            'contact_number' => 'nullable|string|max:15',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $foundItem = FoundItem::create([
            'found_date' => $request->input('found_date'),
            'item_name' => $request->input('item_name'),
            'facebook_link' => $request->input('facebook_link'),
            'contact_number' => $request->input('contact_number'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'location' => $request->input('location'),
            'user_id' => $request->input('user_id'),
            'image_url' => $imageUrl,
        ]);

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
     * Show the form for editing the specified found item.
     */
    public function edit($id)
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

        $imageUrl = $foundItem->image_url;
        if ($request->hasFile('image_url')) {
            $image = $request->file('image_url');
            $filename = time() . '.' . $image->extension();
            $image->move(public_path('assets/img'), $filename);
            $imageUrl = asset('assets/img/' . $filename);

            // Delete the old image if it exists
            if ($foundItem->image_url && file_exists(public_path($foundItem->image_url))) {
                unlink(public_path($foundItem->image_url));
            }
        }

        $request->validate([
            'found_date' => 'required|date',
            'item_name' => 'required|string|max:255',
            'facebook_link' => 'nullable|url',
            'contact_number' => 'nullable|string|max:15',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $foundItem->update([
            'found_date' => $request->input('found_date'),
            'item_name' => $request->input('item_name'),
            'facebook_link' => $request->input('facebook_link'),
            'contact_number' => $request->input('contact_number'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'location' => $request->input('location'),
            'user_id' => $request->input('user_id'),
            'image_url' => $imageUrl,
        ]);

        return response()->json($foundItem);
    }

    /**
     * Remove the specified found item from storage.
     */
    public function destroy($id)
    {
        $foundItem = FoundItem::findOrFail($id);
        
        $filePath = public_path($foundItem->image_url);
        if (file_exists($filePath)) {
            unlink($filePath); 
        } 
    
        $foundItem->delete();
    
        return response()->json(null, 204);
    }
}
