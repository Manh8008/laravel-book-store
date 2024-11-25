<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    use HasFactory;
    protected $table = "books";
    protected $fillable = [
        'name',
        'title',
        'description',
        'price',
        'stock',
        'category_id',
        'author_id',
        'weight',
        'size',
        'pages',
        'language',
        'format',
        'short_summary',
        'publisher',
        'sales_count'
    ];

    // Mỗi sách thuộc 1 danh mục
    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    // 1 sách 1 tác giả . 
    public function author()
    {
        return $this->belongsTo(Authors::class);
    }

    // 1 sách có nhiều hình ảnh
    public function images()
    {
        return $this->hasMany(Images::class, 'book_id');
    }

    // public function reviews()
    // {
    //     return $this->hasMany(Reviews::class, 'book_id');
    // }

    // Each book can be in many order details (many-to-many through order details)
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'book_id');
    }   

    public function comments()
    {
        return $this->hasMany(Comment::class, 'book_id');
    }
}


