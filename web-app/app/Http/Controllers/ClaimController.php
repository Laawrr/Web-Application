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
        // Handle file upload
        $imageUrl = null;
        if ($request->hasFile('proof_of_ownership')) {
            $image = $request->file('proof_of_ownership');
            $filename = time() . '.' . $image->extension();
            $image->move(public_path('assets/proof'), $filename);
            $imageUrl = 'assets/proof/' . $filename;
        }

        // Validate the request
        $validated = $request->validate([
            'item_id' => 'required|exists:found_items,item_id',
            'user_id' => 'required|exists:users,user_id',
            'claim_status' => 'required|in:Pending,Approved,Rejected',
            'submission_date' => 'required|date',
        ]);

        // Create the claim
        $claim = Claim::create([
            'item_id' => $validated['item_id'],
            'user_id' => $validated['user_id'],
            'claim_status' => $validated['claim_status'],
            'submission_date' => $validated['submission_date'],
            'proof_of_ownership' => $imageUrl,
        ]);

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
        $item = FoundItem::with('user')->find($itemId);

        return Inertia::render('Claim', [
            'item' => $item,
            'itemType' => $itemType
        ]);
    }
}
