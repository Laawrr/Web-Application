<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'text',
        'commentable_id',
        'commentable_type',
        'created_at',
        'updated_at'
    ];

    protected $with = ['user'];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Polymorphic relationship
    public function commentable()
    {
        return $this->morphTo();
    }
}
