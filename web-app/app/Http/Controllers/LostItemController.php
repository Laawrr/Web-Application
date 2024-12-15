<?php

namespace App\Http\Controllers;

use App\Models\LostItem;
use Illuminate\Http\Request;

class LostItemController extends Controller
{
    public function index()
    {
        return LostItem::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'description' => 'required|string',
            'category' => 'required|string',
            'image_url' => 'required|url',
            'location_lost' => 'required|string',
            'date_lost' => 'required|date',
        ]);

        return LostItem::create($request->all());
    }

    public function show($id)
    {
        return LostItem::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $lostItem = LostItem::findOrFail($id);
        $lostItem->update($request->all());

        return $lostItem;
    }

    public function destroy($id)
    {
        $lostItem = LostItem::findOrFail($id);
        $lostItem->delete();

        return response()->noContent();
    }
}
