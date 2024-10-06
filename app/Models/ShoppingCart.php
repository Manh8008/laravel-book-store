<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    use HasFactory;
    protected $table = 'shopping_cart';

    // Each shopping cart belongs to one customer
    public function customer()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    // Each shopping cart item belongs to one book
    public function book()
    {
        return $this->belongsTo(Books::class, 'book_id');
    }
}