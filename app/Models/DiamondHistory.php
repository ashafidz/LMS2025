<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiamondHistory extends Model
{
    use HasFactory;
    use HasLocalDates;

    protected $fillable = [
        'user_id',
        'diamonds',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
