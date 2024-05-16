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
        Schema::create('plane_trips', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('plane_id');
            $table->foreign('plane_id')->references('id')->on('planes')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedBigInteger('airport_source_id');
            $table->foreign('airport_source_id')->references('id')->on('airports')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedBigInteger('airport_destination_id');
            $table->foreign('airport_destination_id')->references('id')->on('airports')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedBigInteger('country_source_id')->nullable();
            $table->foreign('country_source_id')->references('id')->on('countries')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedBigInteger('country_destination_id')->nullable();
            $table->foreign('country_destination_id')->references('id')->on('countries')->onUpdate('cascade')->onDelete('cascade');

            $table->decimal('current_price')->default(0);
            $table->integer('available_seats')->default(0);
            $table->date('flight_date');
            $table->date('landing_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plane_trips');
    }
};
