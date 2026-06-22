<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'archetype_name',
        'file_path',
        'original_filename',
        'extracted_text',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
