<?php

namespace App\Models;

use App\Traits\HasHashedRouteKey;
use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;
    use HasHashedRouteKey;
    use HasLocalDates;

    protected $fillable = ['module_id', 'title', 'order', 'lessonable_id', 'lessonable_type'];

    /**
     * Indonesian names of the lesson types, keyed by the lower-case class name of the lessonable model.
     * One list for the learning page, the instructor lesson list and the student home.
     */
    public const TYPE_LABELS = [
        'lessonarticle' => 'Artikel', 'lessonvideo' => 'Video', 'lessondocument' => 'Dokumen',
        'lessonlinkcollection' => 'Tautan', 'lessonpoint' => 'Poin', 'lessonpolling' => 'Polling',
        'lessonwordcloud' => 'Word Cloud', 'lessonassignment' => 'Tugas', 'quiz' => 'Kuis',
    ];

    /**
     * Type name for a lessonable class (full class name or short name), or $fallback when unknown.
     */
    public static function typeLabel(?string $lessonableType, string $fallback = 'Pelajaran'): string
    {
        return self::TYPE_LABELS[strtolower(class_basename((string) $lessonableType))] ?? $fallback;
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the parent lessonable model (article, video, quiz, etc.).
     */
    public function lessonable()
    {
        return $this->morphTo();
    }

    /**
     * The users who have completed this lesson.
     */
    public function completedBy()
    {
        return $this->belongsToMany(User::class, 'lesson_user')->withPivot('completed_at');
    }

    /**
     * The discussion threads for this lesson.
     */
    public function discussions()
    {
        return $this->hasMany(LessonDiscussion::class);
    }
}
