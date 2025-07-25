<?php

namespace App\Models;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'user_id',
        'total_amount',
        'discount_amount',
        'vat_amount',
        'transaction_fee_amount',
        'vat_percentage_at_purchase',
        'final_amount',
        'coupon_id',
        'status',
        'snap_token',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    /**
     * The "booted" method of the model.
     * Secara otomatis membuat order_code unik saat pesanan baru dibuat.
     */
    // protected static function booted()
    // {
    //     static::creating(function ($order) {
    //         // Contoh: INV/20250717/XXXXX
    //         $order->order_code = 'INV/' . now()->format('Ymd') . '/' . strtoupper(uniqid());
    //     });
    // }
    protected static function booted()
    {
        static::creating(function ($order) {
            // DIPERBARUI: Menggunakan tanda hubung (-) bukan garis miring (/)
            // Hasilnya akan seperti: INV-20250718-XXXXX
            $order->order_code = 'INV-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
        });
    }

    /**
     * Mendapatkan pengguna yang melakukan pesanan ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan semua item dalam pesanan ini.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Mendapatkan kupon yang digunakan dalam pesanan ini (jika ada).
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
