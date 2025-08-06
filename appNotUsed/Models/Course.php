<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory, SoftDeletes;
    use HasLocalDates;

    protected $fillable = [
        'instructor_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'status',
        'thumbnail_url',
        // Add the new fields
        'availability_type',
        'start_date',
        'end_date',
        'payment_type', // Tambahkan ini
        'diamond_price', // Tambahkan ini
    ];


    /**
     * Mendapatkan semua topik soal yang terhubung dengan kursus ini.
     */
    public function questionTopics()
    {
        return $this->belongsToMany(QuestionTopic::class, 'course_question_topic');
    }

    /**
     * Mendapatkan catatan poin (dari tabel pivot) yang terkait dengan kursus ini.
     */
    public function points()
    {
        // Menghubungkan Course ke model pivot CourseUser
        return $this->hasMany(CourseUser::class);
    }

    /**
     * Boot method to auto-generate slug from title if not provided.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    /**
     * Get the category that the course belongs to.
     */
    public function category()
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    /**
     * Get the instructor that owns the course.
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * The students that are enrolled in the course.
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'course_enrollments')
            ->withTimestamps()->withPivot('enrolled_at');
    }

    /**
     * Get all of the modules for the Course.
     */
    public function modules()
    {
        return $this->hasMany(Module::class);
    }
    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Module::class);
    }

    /**
     * Mendapatkan ulasan instruktur yang terkait dengan kursus ini.
     */
    public function instructorReviews()
    {
        return $this->hasMany(InstructorReview::class);
    }

    /**
     * Mendefinisikan relasi bahwa satu Course memiliki banyak ulasan (reviews).
     */
    public function reviews()
    {
        // Ganti CourseReview::class dengan nama model ulasan kursus Anda yang sebenarnya.
        return $this->hasMany(CourseReview::class);
    }

    /**
     * Mendapatkan semua sertifikat yang telah diterbitkan untuk kursus ini.
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}
