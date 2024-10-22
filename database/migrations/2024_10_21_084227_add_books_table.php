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
        Schema::table('books', function (Blueprint $table) {
            $table->decimal('weight', 10, 2)->nullable(); // Trọng lượng sách
            $table->string('size', 50)->nullable(); // Kích thước sách
            $table->integer('pages')->unsigned()->nullable(); // Số trang sách
            $table->string('language', 50)->nullable(); // Ngôn ngữ sách
            $table->string('format', 50)->nullable(); // Định dạng sách
            $table->text('short_summary')->nullable(); // Tóm tắt ngắn
            $table->string('publisher', 255)->nullable(); // Nhà xuất bản sách
        });
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'weight', 
                'size', 
                'pages', 
                'language', 
                'format', 
                'short_summary', 
                'publisher'
            ]);
        });
    }
};
