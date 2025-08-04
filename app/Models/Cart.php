<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;
    use HasLocalDates;

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
