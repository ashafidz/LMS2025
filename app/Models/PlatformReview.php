<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rating',
        'comment',
    ];

    /**
     * Mendapatkan pengguna yang memberikan ulasan platform.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
