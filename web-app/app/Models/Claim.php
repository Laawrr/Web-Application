<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    use HasFactory;

    protected $table = 'claims';

    protected $fillable = [
        'item_id',
        'user_id',
        'claim_status',
        'proof_of_ownership',
        'submission_date',
    ];

    public function foundItem()
    {
        return $this->belongsTo(FoundItem::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
