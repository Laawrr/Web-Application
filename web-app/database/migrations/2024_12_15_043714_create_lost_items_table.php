<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // In your create_lost_items_table migration
   public function up()
   {
    Schema::create('lost_items', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->date('lost_date');
        $table->string('owner');
        $table->string('facebook_link');
        $table->string('contact_number');
        $table->string('image_path')->nullable();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });    
   }
   
   public function down()
   {
       Schema::dropIfExists('lost_items');
   }
   

};
