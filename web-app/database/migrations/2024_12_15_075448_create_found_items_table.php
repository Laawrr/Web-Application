<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoundItemsTable extends Migration
{
    public function up()
    {
        Schema::create('found_items', function (Blueprint $table) {
            $table->id('found_item_id');
            $table->unsignedBigInteger('user_id');
            $table->text('description');
            $table->string('category');
            $table->text('image_url');
            $table->string('location_found');
            $table->date('date_found');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('found_items');
    }
}