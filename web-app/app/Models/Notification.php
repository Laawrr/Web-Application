<?php

// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    // The attributes that are mass assignable
    protected $fillable = [
        'type',
        'data',
        'read_at',
        'user_id',
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // Define a polymorphic relationship with the notifiable model
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    // Define a relationship with the user who will receive the notification
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
