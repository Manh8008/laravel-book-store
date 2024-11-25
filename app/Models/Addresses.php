<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    use HasFactory;
    protected $table = 'addresses';
    protected $fillable = [
        'address_line',
        'phone',
        'name',
        'user_id',
        'town',
        'district',
        'province',
        'default',
        'townCode',
        'districtCode',
        'provinceCode',
    ];

    // Each address belongs to one customer
    public function user()
    {
        return $this->belongsTo(Users::class,'user_id');
    }

    // Each address can be associated with many orders
    public function orders()
    {
        return $this->hasMany(Orders::class, 'address_id');
    }
}
