<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;
    // protected $filltable = [''];
    protected $table = 'categories';
    protected $fillable = [
        'name',
        'url',
    ];
    public function books()
    {
        return $this->hasMany(Books::class);
    }
}

