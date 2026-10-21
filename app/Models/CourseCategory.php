<?php

namespace App\Models;

use App\Traits\HasHashedRouteKey;
use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCategory extends Model
{
    use HasFactory;
    use HasHashedRouteKey;
    use HasLocalDates;

    protected $fillable = ['name', 'slug'];

    /**
     * Get all of the courses for the CourseCategory.
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'category_id');
    }
}
