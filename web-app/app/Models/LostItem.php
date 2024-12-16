<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostItem extends Model
{
    use HasFactory;

    // Optionally specify the table if it's not named 'lost_items' by convention
    protected $table = 'lost_items';

    // Add the fillable properties to allow mass assignment
    protected $fillable = [
        'name',
        'lost_date',
        'owner',
        'facebook_link',
        'contact_number',
        'image_path',
    ];
}
