<?php

// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $keyType = 'string';  // For UUID as primary key
    protected $primaryKey = 'id';   // Specify the primary key

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'user_id',
    ];

    protected $casts = [
        'data' => 'array',  // Automatically cast 'data' field to an array
        'read_at' => 'datetime',  // Automatically cast 'read_at' field to a datetime
    ];

    // Polymorphic relation: this allows the notification to be related to various other models
    public function notifiable()
    {
        return $this->morphTo();
    }

    // Relationship to User model (notification belongs to user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
