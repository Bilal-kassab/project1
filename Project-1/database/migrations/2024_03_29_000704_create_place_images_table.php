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
        Schema::create('place_images', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('place_id');
            $table->foreign('place_id')->references('id')->on('places')->onUpdate('cascade')->onDelete('cascade');

            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_images');
    }
};
