<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoundItem extends Model
{
    use HasFactory;

    protected $table = 'found_items';

    protected $fillable = [
        'user_id',
        'description',
        'category',
        'image_url',
        'location_found',
        'date_found',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
