<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClaimsTable extends Migration
{
    public function up()
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id('claim_id');
            $table->unsignedBigInteger('lost_item_id');
            $table->unsignedBigInteger('found_item_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('claim_status', ['Pending', 'Approved', 'Rejected']);
            $table->text('proof_of_ownership')->nullable();
            $table->date('submission_date');
            $table->foreign('lost_item_id')->references('lost_item_id')->on('lost_items')->onDelete('cascade');
            $table->foreign('found_item_id')->references('found_item_id')->on('found_items')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('claims');
    }
}