<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            // Auto-incrementing primary key
            $table->id();
            
            // Type of notification (e.g., new_comment, mention, etc.)
            $table->string('type');
            
            // Polymorphic relationship for various notifiable models (e.g., LostItem, FoundItem, etc.)
            $table->morphs('notifiable');
            
            // Data related to the notification, stored as JSON
            $table->text('data');
            
            // Timestamp when the notification was marked as read
            $table->timestamp('read_at')->nullable();
            
            // Timestamps for created_at and updated_at
            $table->timestamps();
            
            // User who will receive the notification
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
