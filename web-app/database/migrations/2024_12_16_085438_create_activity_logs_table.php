<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('log_id'); // Auto-increment primary key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign key reference to users table
            $table->string('action'); // Action description
            $table->timestamp('action_time')->useCurrent(); // Action timestamp (default to current time)
            $table->string('ip_address', 45)->nullable(); // IP address
            $table->text('user_agent')->nullable(); // User agent
            $table->timestamps(); // created_at, updated_at (optional but useful for tracking record creation and modification)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
}
