<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLostItemsTable extends Migration
{
    public function up()
    {
        Schema::create('lost_items', function (Blueprint $table) {
            $table->id('lost_item_id');
            $table->unsignedBigInteger('user_id');
            $table->text('description');
            $table->string('category');
            $table->text('image_url');
            $table->string('location_lost');
            $table->date('date_lost');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lost_items');
    }
}