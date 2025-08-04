<?php
// simpan di app/Traits/HasLocalDates.php

namespace App\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

trait HasLocalDates
{
    /**
     * Helper function untuk mendapatkan zona waktu lokal yang benar.
     * Prioritas: DB Pengguna -> Session -> Config Default
     *
     * @return string
     */
    private function getLocalTimezone(): string
    {
        // Prioritas 1: Ambil dari data pengguna yang sedang login (paling andal)
        if (Auth::check() && Auth::user()->timezone) {
            return Auth::user()->timezone;
        }

        // Prioritas 2: Fallback ke session (untuk tamu atau sebelum data DB diupdate)
        if (session('user_timezone')) {
            return session('user_timezone');
        }

        // Prioritas 3: Fallback ke config default aplikasi
        return config('app.timezone');
    }

    /**
     * Accessor untuk kolom 'created_at' (umum).
     */
    public function getCreatedAtAttribute($value): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }
        return Carbon::parse($value)->tz($this->getLocalTimezone());
    }

    /**
     * Accessor untuk kolom 'updated_at' (umum).
     */
    public function getUpdatedAtAttribute($value): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }
        return Carbon::parse($value)->tz($this->getLocalTimezone());
    }

    /**
     * Accessor untuk kolom 'deleted_at' (umum untuk SoftDeletes).
     */
    public function getDeletedAtAttribute($value): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }
        return Carbon::parse($value)->tz($this->getLocalTimezone());
    }

    /**
     * Accessor untuk model AssignmentSubmission.
     */
    public function getSubmittedAtAttribute($value): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }
        return Carbon::parse($value)->tz($this->getLocalTimezone());
    }

    /**
     * Accessor untuk model Certificate.
     */
    public function getIssuedAtAttribute($value): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }
        return Carbon::parse($value)->tz($this->getLocalTimezone());
    }

    /**
     * Accessor untuk model Coupon (mulai berlaku).
     */
    public function getStartsAtAttribute($value): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }
        return Carbon::parse($value)->tz($this->getLocalTimezone());
    }

    /**
     * Accessor untuk model Coupon (kadaluarsa).
     */
    public function getExpiresAtAttribute($value): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }
        return Carbon::parse($value)->tz($this->getLocalTimezone());
    }

    /**
     * Accessor untuk model Course (tanggal mulai).
     */
    public function getStartDateAttribute($value): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }
        return Carbon::parse($value)->tz($this->getLocalTimezone());
    }

    /**
     * Accessor untuk model Course (tanggal selesai).
     */
    public function getEndDateAttribute($value): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }
        return Carbon::parse($value)->tz($this->getLocalTimezone());
    }

    /**
     * Accessor untuk model LessonAssignment (batas waktu).
     */
    public function getDueDateAttribute($value): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }
        return Carbon::parse($value)->tz($this->getLocalTimezone());
    }

    /**
     * Accessor untuk model QuizAttempt (waktu mulai).
     */
    public function getStartTimeAttribute($value): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }
        return Carbon::parse($value)->tz($this->getLocalTimezone());
    }

    /**
     * Accessor untuk model QuizAttempt (waktu selesai).
     */
    public function getEndTimeAttribute($value): ?Carbon
    {
        if (is_null($value)) {
            return null;
        }
        return Carbon::parse($value)->tz($this->getLocalTimezone());
    }
}
