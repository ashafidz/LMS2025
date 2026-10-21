<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
