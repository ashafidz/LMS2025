<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use App\Traits\HasHashedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseCategory extends Model
{
    use HasFactory;
    use HasLocalDates;
    use HasHashedRouteKey;

    protected $fillable = ['name', 'slug'];

    /**
     * Get all of the courses for the CourseCategory.
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'category_id');
    }
}
