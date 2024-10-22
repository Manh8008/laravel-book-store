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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id(); // Creates an auto-incrementing ID column
            $table->text('description'); // Creates a text column for the review description
            $table->string('image_url'); // Creates a string column for the image URL
            $table->timestamps(); // Adds created_at and updated_at timestamp columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews'); // Drops the reviews table if it exists
    }
};
