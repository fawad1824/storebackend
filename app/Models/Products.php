<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;
    protected $table = "products";
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'price',
        'qty',
        'description',
        'image',
        'gallery',
        'discount',
        'is_lan'
    ];
}
