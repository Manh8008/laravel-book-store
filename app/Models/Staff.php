<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;  // Đảm bảo kế thừa từ User
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Staff extends Authenticatable  // Kế thừa Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'staff';
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Thêm các thuộc tính và phương thức liên quan đến xác thực nếu cần
}

