<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;
use App\Models\Comment;

class FoundItem extends Model
{
    use HasFactory;

    protected $table = 'found_items';

    protected $fillable = [
        'item_name', 'found_date', 'facebook_link', 'contact_number', 
        'description', 'category', 'location', 'image_url', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function claim()
    {
        return $this->hasOne(Claim::class, 'item_id');
    }

    /**
     * Get all of the comments for the FoundItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function notifyUser()
    {
        // Create a new notification
        Notification::create([
            'type' => 'found_item_reported',
            'data' => ['item_name' => $this->item_name],
            'user_id' => $this->user_id,
        ]);
    }
}
