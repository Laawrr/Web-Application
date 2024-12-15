<?php

namespace App\Http\Controllers;

use App\Models\FoundItem;
use Illuminate\Http\Request;

class FoundItemController extends Controller
{
    public function index()
    {
        return FoundItem::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'description' => 'required|string',
            'category' => 'required|string',
            'image_url' => 'required|url',
            'location_found' => 'required|string',
            'date_found' => 'required|date',
        ]);

        return FoundItem::create($request->all());
    }

    public function show($id)
    {
        return FoundItem::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $foundItem = FoundItem::findOrFail($id);
        $foundItem->update($request->all());

        return $foundItem;
    }

    public function destroy($id)
    {
        $foundItem = FoundItem::findOrFail($id);
        $foundItem->delete();

        return response()->noContent();
    }
}
