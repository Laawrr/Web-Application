<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function index()
    {
        return Claim::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'lost_item_id' => 'required|exists:lost_items,lost_item_id',
            'found_item_id' => 'required|exists:found_items,found_item_id',
            'user_id' => 'required|exists:users,user_id',
            'claim_status' => 'required|in:Pending,Approved,Rejected',
            'proof_of_ownership' => 'nullable|string',
            'submission_date' => 'required|date',
        ]);

        return Claim::create($request->all());
    }

    public function show($id)
    {
        return Claim::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);
        $claim->update($request->all());

        return $claim;
    }

    public function destroy($id)
    {
        $claim = Claim::findOrFail($id);
        $claim->delete();

        return response()->noContent();
    }
}
