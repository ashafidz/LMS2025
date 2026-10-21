<?php

namespace App\Services;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Serializes the write actions of one student.
 *
 * Runs the callback in a transaction while holding a row lock on the student's `users` row, so two requests from
 * the same student (double click, browser retry, auto-submit + manual submit) run one after another instead of both
 * passing a "has this already happened?" check. Other students are never blocked. Repeat every such check inside
 * the callback. The lock is ignored on SQLite (tests), where requests never overlap.
 */
class StudentWriteLock
{
    public static function run(User $user, Closure $callback): mixed
    {
        // Retry a few times if MySQL reports a deadlock
        return DB::transaction(function () use ($user, $callback) {
            User::whereKey($user->getKey())->lockForUpdate()->value('id');

            return $callback();
        }, 3);
    }
}
