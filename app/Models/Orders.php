<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $fillable = [
        'user_id', 'address_id', 'payment_id', 'order_date', 'order_price', 
        'order_code', 'total_amount', 'payment_status', 'order_status', 
        'address_line', 'city', 'phone', 'name'
    ];


    public function address()
    {
        return $this->belongsTo(Addresses::class, 'address_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payments::class, 'payment_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}

