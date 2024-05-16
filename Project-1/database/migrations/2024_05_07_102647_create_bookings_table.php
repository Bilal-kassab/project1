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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedBigInteger('source_trip_id')->nullable();
            $table->foreign('source_trip_id')->references('id')->on('countries')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedBigInteger('destination_trip_id')->nullable();
            $table->foreign('destination_trip_id')->references('id')->on('countries')->onUpdate('cascade')->onDelete('cascade');

            $table->string('trip_name');
            $table->decimal('price')->default(0);
            $table->integer('number_of_people')->default(1);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('stars')->default(0);
            $table->string('trip_note');
            $table->enum('type',['static','dynamic']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
