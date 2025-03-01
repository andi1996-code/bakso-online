<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'quantity'];
    //cart belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
