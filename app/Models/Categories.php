<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;
    // protected $filltable = [''];
    protected $table = 'categories';

    // Each category can have many books
    public function books()
    {
        return $this->hasMany(Books::class, 'category_id');
    }
}

