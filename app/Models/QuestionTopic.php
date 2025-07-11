<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionTopic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'instructor_id',
        'name',
        'description',
    ];

    /**
     * The instructor who owns this topic.
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * The questions that belong to this topic.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'topic_id');
    }
}
