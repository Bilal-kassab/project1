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
        Schema::create('airport_images', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('plane_id');
            $table->foreign('plane_id')->references('id')->on('planes')->onUpdate('cascade')->onDelete('cascade');

            $table->string('image')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airport_images');
    }
};
