<?php

namespace App\Http\Controllers;

use App\Models\LostItem;
use Illuminate\Http\Request;

class LostItemController extends Controller
{
    /**
     * Display a listing of the lost items.
     */
    public function index()
    {
        $lostItems = LostItem::all();
        return response()->json($lostItems);
    }

    /**
     * Show the form for creating a new lost item.
     */
    public function create()
    {
        return response()->json('Create lost item form.');
    }

    /**
     * Store a newly created lost item in storage.
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
            'lost_date' => 'required|date',
            'item_name' => 'required|string|max:255',
            'facebook_link' => 'nullable|url',
            'contact_number' => 'nullable|string|max:15',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'location' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $lostItem = LostItem::create([
            'lost_date' => $request->input('lost_date'),
            'item_name' => $request->input('item_name'),
            'facebook_link' => $request->input('facebook_link'),
            'contact_number' => $request->input('contact_number'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'location' => $request->input('location'),
            'user_id' => $request->input('user_id'),
            'image_url' => $imageUrl,
        ]);

        return redirect()->back()->with('success', 'Lost item created successfully.');
    }

    /**
     * Display the specified lost item.
     */
    public function show($id)
    {
        $lostItem = LostItem::with('user')->findOrFail($id);
        return response()->json($lostItem);
    }

    /**
     * Show the form for editing the specified lost item.
     */
    public function edit($id)
    {
        $lostItem = LostItem::findOrFail($id);
        return response()->json($lostItem);
    }

    /**
     * Update the specified lost item in storage.
     */
    public function update(Request $request, $id)
    {
        $lostItem = LostItem::findOrFail($id);

        $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'facebook_link' => 'nullable|url',
            'contact_number' => 'nullable|string|max:15',
        ]);

        $data = $request->only([
            'item_name',
            'category',
            'description',
            'facebook_link',
            'contact_number',
        ]);

        if ($request->hasFile('image_url')) {
            $image = $request->file('image_url');
            $filename = time() . '.' . $image->extension();
            $image->move(public_path('assets/img'), $filename);
            $data['image_url'] = 'assets/img/' . $filename;
        }

        $lostItem->update($data);

        return response()->json([
            'message' => 'Lost item updated successfully',
            'data' => $lostItem
        ]);
    }

    /**
     * Remove the specified lost item from storage.
     */
    public function destroy($id)
    {
        $lostItem = LostItem::findOrFail($id);
        
        $filePath = public_path($lostItem->image_url);
        if (file_exists($filePath)) {
            unlink($filePath); 
        } 
    
        $lostItem->delete();
    
        return response()->json(null, 204);
    }
}
