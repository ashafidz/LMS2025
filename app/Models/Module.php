<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use App\Traits\HasHashedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;
    use HasLocalDates;
    use HasHashedRouteKey;

    protected $fillable = ['course_id', 'title', 'order', 'points_required',];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
}
