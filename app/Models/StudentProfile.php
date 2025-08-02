<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'headline',
        'website_url',
        'student_status',
        'bio',
        'highest_level_of_education',
        'profession',
        'company_or_institution_name',
        'company_address',
        'company_tax_id',
        'unique_id_number',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
