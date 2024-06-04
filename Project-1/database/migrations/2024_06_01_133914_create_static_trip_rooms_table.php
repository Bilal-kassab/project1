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
        Schema::create('static_trip_rooms', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('booking_static_trip_id');
            $table->foreign('booking_static_trip_id')->references('id')->on('booking_static_trips')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedBigInteger('room_id');
            $table->foreign('room_id')->references('id')->on('rooms')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('static_trip_rooms');
    }
};
