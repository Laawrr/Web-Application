<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use Illuminate\Http\Request;

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

    public function show($id)
    {
        $claim = Claim::findOrFail($id);
        return response()->json($claim);
    }
}
