<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClaimController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lost_item_id' => 'required|exists:lost_items,lost_item_id',
            'found_item_id' => 'required|exists:found_items,found_item_id',
            'user_id' => 'required|exists:users,user_id',
            'claim_status' => 'required|in:Pending,Approved,Rejected',
            'proof_of_ownership' => 'nullable|string',
            'submission_date' => 'required|date',
        ]);

        $claim = Claim::create($validated);

        return response()->json($claim, 201);
    }

    public function update(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);

        // Validate the claim status
        $request->validate([
            'claim_status' => 'required|in:Pending,Approved,Rejected',
        ]);

        $claim->updateClaimStatus($request->claim_status);

        return response()->json($claim);
    }

    public function updateStatus(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $claim->update([
            'claim_status' => ucfirst($validated['status'])
        ]);

        return back()->with('success', 'Claim status updated successfully');
    }

    public function show($id)
    {
        $claim = Claim::findOrFail($id);
        return response()->json($claim);
    }

    public function index(Request $request)
    {
        $itemId = $request->query('item_id');
        $itemType = $request->query('item_type');

        $item = null;
        if ($itemType === 'lost') {
            $item = LostItem::with('user')->find($itemId);
        } else if ($itemType === 'found') {
            $item = FoundItem::with('user')->find($itemId);
        }

        return Inertia::render('Claim', [
            'item' => $item,
            'itemType' => $itemType
        ]);
    }
}
