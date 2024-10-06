<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookDetail extends Model
{
    use HasFactory;
    protected $table = 'book_details'; // Tên bảng

    // Thiết lập mối quan hệ với model Book
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
