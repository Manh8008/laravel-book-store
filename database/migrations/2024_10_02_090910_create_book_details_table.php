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
        Schema::create('book_details', function (Blueprint $table) {
            $table->id(); // Tạo trường id tự động tăng            
            $table->unsignedBigInteger('book_id');
            $table->string('isbn')->unique(); // ISBN sách
            $table->decimal('weight', 10, 2)->nullable(); // Trọng lượng sách
            $table->string('size', 50)->nullable(); // Kích thước sách
            $table->integer('pages')->unsigned()->nullable(); // Số trang sách
            $table->string('language', 50)->nullable(); // Ngôn ngữ sách
            $table->string('format', 50)->nullable(); // Định dạng sách
            $table->text('short_summary')->nullable(); // Tóm tắt ngắn
            $table->string('publisher', 255)->nullable(); // Nhà xuất bản sách
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');

            $table->timestamps(); // Thời gian tạo và cập nhật
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_details');
    }
};
