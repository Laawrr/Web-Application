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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lostItem()
    {
        return $this->belongsTo(LostItem::class, 'lost_item_id');
    }

    public function foundItem()
    {
        return $this->belongsTo(FoundItem::class, 'found_item_id');
    }
}
