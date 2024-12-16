<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    use HasFactory;

    protected $table = 'claims';

    protected $fillable = [
        'lost_item_id',
        'found_item_id',
        'user_id',
        'claim_status',
        'proof_of_ownership',
        'submission_date',
    ];

    public function lostItem()
    {
        return $this->belongsTo(LostItem::class, 'lost_item_id');
    }

    public function foundItem()
    {
        return $this->belongsTo(FoundItem::class, 'found_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Method to update the status and move approved claims
    public function updateClaimStatus($status)
    {
        $this->claim_status = $status;
        $this->save();

        if ($status == 'Approved') {
            $this->moveToFoundItems();
        }
    }

    private function moveToFoundItems()
    {
        // Move lost item to found items (this assumes you have logic for lost items)
        $lostItem = LostItem::find($this->lost_item_id);
        if ($lostItem) {
            $foundItem = FoundItem::create([
                'user_id' => $this->user_id,
                'description' => $lostItem->description,
                'category' => $lostItem->category,
                'image_url' => $lostItem->image_url,
                'location_found' => $lostItem->location_found,
                'date_found' => now(), // Or use the lost item's date
            ]);
            $this->found_item_id = $foundItem->found_item_id;
            $this->save();
        }
    }
}
