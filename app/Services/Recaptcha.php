<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google reCAPTCHA v3 verification shared by the contact form and the auth forms.
 * Empty keys mean captcha is off. The score and the token are never shown to users.
 */
class Recaptcha
{
    public const MISSING = 'Pemeriksaan keamanan gagal. Muat ulang halaman lalu coba lagi.';

    public const UNREACHABLE = 'Pemeriksaan keamanan sedang tidak bisa dihubungi. Silakan coba lagi beberapa saat lagi.';

    public const SUSPICIOUS = 'Permintaan Anda terdeteksi mencurigakan. Muat ulang halaman lalu coba lagi.';

    public static function enabled(): bool
    {
        return filled(config('services.recaptcha.secret_key')) && filled(config('services.recaptcha.site_key'));
    }

    /**
     * Null when the check passes, otherwise one of: missing, unreachable, score, action.
     */
    public static function failure(?string $token, string $action, ?string $ip): ?string
    {
        if (blank($token)) {
            return 'missing';
        }

        try {
            $response = Http::asForm()->timeout(10)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $token,
                'remoteip' => $ip,
            ]);
        } catch (ConnectionException $e) {
            Log::warning('reCAPTCHA tidak bisa dihubungi: '.$e->getMessage());

            return 'unreachable';
        }

        $result = $response->json();
        if (! $response->successful() || ! is_array($result) || ! array_key_exists('success', $result)) {
            Log::warning('Jawaban reCAPTCHA tidak dikenali', ['status' => $response->status()]);

            return 'unreachable';
        }

        // v3 always sends score and action; without them (wrong key type) the check fails
        $score = $result['score'] ?? null;
        if ($result['success'] !== true || ! is_numeric($score) || (float) $score < (float) config('services.recaptcha.min_score', 0.5)) {
            return 'score';
        }

        return ($result['action'] ?? null) === $action ? null : 'action';
    }

    /**
     * Null when the user may continue, otherwise a plain Indonesian message. With $failOpen an unreachable Google lets the
     * user through (logged), so a Google outage cannot lock people out of login.
     */
    public static function problem(?string $token, string $action, ?string $ip, bool $failOpen = false): ?string
    {
        $reason = self::failure($token, $action, $ip);

        return match (true) {
            $reason === null => null,
            $reason === 'unreachable' && $failOpen => null,
            $reason === 'unreachable' => self::UNREACHABLE,
            $reason === 'missing' => self::MISSING,
            default => self::SUSPICIOUS,
        };
    }
}
