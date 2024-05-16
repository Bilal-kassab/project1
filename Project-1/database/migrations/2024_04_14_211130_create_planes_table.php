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
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('number_of_seats');
            $table->boolean('visible')->default(true);
            $table->decimal('ticket_price');

            $table->unsignedBigInteger('airport_id')->nullable();
            $table->foreign('airport_id')->references('id')->on('airports')->onUpdate('cascade')->onDelete('cascade');

            // $table->unsignedBigInteger('airport_destination_id')->nullable();
            // $table->foreign('airport_destination_id')->references('id')->on('airports')->onUpdate('cascade')->onDelete('set null');





            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('planes');
    }
};
