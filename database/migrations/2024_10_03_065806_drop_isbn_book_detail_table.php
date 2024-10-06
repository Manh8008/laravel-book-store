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
        Schema::table('book_details', function (Blueprint $table) {
            $table->dropColumn('isbn'); // Xóa cột isbn
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_details', function (Blueprint $table) {
            $table->string('isbn')->nullable(); // Khôi phục lại cột isbn
        });
    }
};
