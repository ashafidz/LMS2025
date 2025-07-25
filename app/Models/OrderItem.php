<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'course_id',
        'price_at_purchase',
    ];

    protected $casts = [
        'price_at_purchase' => 'decimal:2',
    ];

    /**
     * Mendapatkan pesanan (order) dari item ini.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Mendapatkan kursus dari item ini.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
