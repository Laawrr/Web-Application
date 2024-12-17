<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoundItem extends Model
{
    use HasFactory;

    protected $table = 'found_items';

    protected $fillable = [
        'item_name','found_date', 'facebook_link', 'contact_number', 
        'description', 'category', 'location', 'image_url', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function claim()
    {
        return $this->hasOne(Claim::class, 'found_item_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

}
