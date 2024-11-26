<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reviews extends Model
{
    use HasFactory;
    protected $table = 'reviews';
    protected $fillable = [
        'title',
        'description',
        'image_url',
    ];

    public function book()
    {
        return $this->belongsTo(Books::class, 'book_id');
    }
}
