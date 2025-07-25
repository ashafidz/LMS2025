<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
    ];

    /**
     * Mendapatkan pengguna yang memiliki item keranjang ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan kursus yang ada di keranjang ini.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
