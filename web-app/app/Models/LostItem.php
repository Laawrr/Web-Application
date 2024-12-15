<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostItem extends Model
{
    use HasFactory;

    protected $table = 'lost_items';

    protected $fillable = [
        'user_id',
        'description',
        'category',
        'image_url',
        'location_lost',
        'date_lost',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
