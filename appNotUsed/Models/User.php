<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\InstructorProfile;
use App\Models\QuestionTopic;
use App\Models\StudentProfile;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string $status
 * @property string|null $profile_picture_url
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read InstructorProfile|null $instructorProfile
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfilePictureUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone_number',
        'address',
        'gender',
        'birth_date',
        'profile_picture_url',
        'diamond_balance',
        'equipped_badge_id'
    ];

    // Tambahkan relasi baru ini
    public function diamondHistories()
    {
        return $this->hasMany(DiamondHistory::class);
    }

    public function coursePoints()
    {
        return $this->belongsToMany(Course::class, 'course_user')->withPivot('points_earned', 'is_converted_to_diamond');
    }

    /**
     * Mendapatkan semua badge yang dimiliki oleh pengguna ini.
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class);
    }

    /**
     * Mendapatkan badge yang sedang dipasang oleh pengguna ini.
     */
    public function equippedBadge()
    {
        return $this->belongsTo(Badge::class, 'equipped_badge_id');
    }

    public function pointHistories()
    {
        return $this->hasMany(PointHistory::class);
    }


    public function instructorProfile(): HasOne
    {
        return $this->hasOne(InstructorProfile::class);
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function questionTopics(): HasMany
    {
        return $this->hasMany(QuestionTopic::class, 'instructor_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /**
     * The courses that the user is enrolled in.
     */
    public function enrollments()
    {
        return $this->belongsToMany(Course::class, 'course_enrollments')
            ->withTimestamps()->withPivot('enrolled_at');
    }

    public function completedLessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_user')->withPivot('completed_at');
    }

    public function courseReviews()
    {
        return $this->hasMany(CourseReview::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'student_id');
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function lessonDiscussions()
    {
        return $this->hasMany(LessonDiscussion::class);
    }

    /**
     * Mendapatkan item keranjang belanja milik pengguna.
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Mendapatkan riwayat pesanan milik pengguna.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Mendapatkan ulasan instruktur yang diberikan oleh pengguna ini.
     */
    public function instructorReviews()
    {
        return $this->hasMany(InstructorReview::class);
    }

    /**
     * Mendapatkan jawaban skala Likert yang diberikan oleh pengguna ini.
     */
    public function likertAnswers()
    {
        return $this->hasMany(LikertAnswer::class);
    }

    /**
     * Mendapatkan semua sertifikat yang dimiliki oleh pengguna ini.
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Mendapatkan ulasan platform yang diberikan oleh pengguna ini.
     */
    public function platformReview()
    {
        return $this->hasOne(PlatformReview::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
