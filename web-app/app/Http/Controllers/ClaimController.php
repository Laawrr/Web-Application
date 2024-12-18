<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\FoundItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClaimController extends Controller
{
    /**
     * Store a new claim.
     */
    public function store(Request $request)
    {
        // Get the authenticated user's ID directly
        $userID = Auth::id(); // Always use the Authenticated user's ID
    
        // If no submission date is provided, set it to the current date
        $submissionDate = $request->submission_date ? Carbon::parse($request->submission_date)->toDateString() : Carbon::now()->toDateString();
    
        // Validate the request
        $validated = $request->validate([
            'item_id' => 'required|exists:found_items,id',
            'claim_status' => 'required|in:Pending,Approved,Rejected',
            'submission_date' => 'nullable|date', // Make submission_date nullable
            'proof_of_ownership' => 'nullable|file|mimes:jpeg,png,pdf|max:2048', // File validation
        ]);
    
        // Handle file upload
        $imageUrl = null;
        if ($request->hasFile('proof_of_ownership')) {
            $filename = time() . '.' . $request->proof_of_ownership->extension();
            $imageUrl = $request->file('proof_of_ownership')->storeAs('assets/proof', $filename, 'public');
        }
    
        // Create the claim with the authenticated user's ID
        $claim = Claim::create([
            'item_id' => $validated['item_id'],
            'user_id' => $userID, // Use the authenticated user's ID
            'claim_status' => $validated['claim_status'],
            'submission_date' => $submissionDate, // Use the provided date or current date
            'proof_of_ownership' => $imageUrl,
        ]);
    
        // Instead of returning an Inertia response, return a redirect
        return redirect('/');  // Redirect to the homepage or any other page you'd like after submission
    }
    
    

    /**
     * Update the claim's status.
     */
    public function update(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);

        // Validate the claim status
        $validated = $request->validate([
            'claim_status' => 'required|in:Pending,Approved,Rejected',
        ]);

        $claim->update([
            'claim_status' => $validated['claim_status'],
        ]);

        return response()->json($claim);
    }

    /**
     * Update the claim status (another method, for UI handling).
     */
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

    /**
     * Show a specific claim.
     */
    public function show($id)
    {
        $claim = Claim::findOrFail($id);
        return response()->json($claim);
    }

    /**
     * Index claims with item details.
     */
    public function index(Request $request)
    {
        $itemId = $request->query('item_id');
        $itemType = $request->query('item_type');

        $item = FoundItem::with('user')->find($itemId);

        return Inertia::render('Claim', [
            'item' => $item,
            'itemType' => $itemType,
        ]);
    }
}
