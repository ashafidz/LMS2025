<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $headline
 * @property string|null $bio
 * @property string|null $website_url
 * @property string $application_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstructorProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstructorProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstructorProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstructorProfile whereApplicationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstructorProfile whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstructorProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstructorProfile whereHeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstructorProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstructorProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstructorProfile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstructorProfile whereWebsiteUrl($value)
 * @mixin \Eloquent
 */
class InstructorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'headline',
        'website_url',
        'application_status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
