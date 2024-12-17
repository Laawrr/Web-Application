<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostItem extends Model
{
    use HasFactory;

    protected $table = 'lost_items';

    protected $fillable = [
        'item_name', 'lost_date', 'facebook_link', 'contact_number', 
        'description', 'category', 'location', 'image_url', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
