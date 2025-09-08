<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlatformReview extends Model
{
    use HasFactory;
    use HasLocalDates;

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
