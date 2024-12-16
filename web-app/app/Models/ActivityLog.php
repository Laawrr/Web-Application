<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    // Allow mass assignment for the following columns
    protected $fillable = ['user_id', 'action', 'action_time', 'ip_address', 'user_agent'];

    // Optionally, define relationships with the User model (if you want to retrieve user data with the log)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
