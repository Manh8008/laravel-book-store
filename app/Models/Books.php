<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    use HasFactory;
    protected $table = "books";

    // Mỗi sách thuộc 1 danh mục
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    // 1 sách 1 tác giả . 
    public function author()
    {
        return $this->belongsTo(Authors::class, 'author_id');
    }

    // 1 sách có nhiều hình ảnh
    public function images()
    {
        return $this->hasMany(Images::class, 'book_id');
    }

    public function reviews()
    {
        return $this->hasMany(Reviews::class, 'book_id');
    }

    // Each book can be in many order details (many-to-many through order details)
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'book_id');
    }

    // mỗi sách có nhiều trong giỏ hàng
    public function shoppingCarts()
    {
        return $this->hasMany(ShoppingCart::class, 'book_id');
    }

    public function details()
    {
        return $this->hasMany(BookDetail::Class, 'book_id');
    }
}


