<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Services\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ContactMessageController extends Controller
{
    public const MAX_MESSAGE_LENGTH = 5000;

    private const SPAM_MESSAGE = 'Pesan tidak terkirim karena terdeteksi sebagai spam. Jika Anda bukan robot, coba lagi beberapa saat lagi.';

    /**
     * Form kontak publik. Skrip template (php-email-form/validate.js) hanya membaca isi respons berstatus 200:
     * "OK" berarti terkirim, teks lain ditampilkan sebagai pesan error. Karena itu error untuk pengunjung dikirim
     * sebagai teks biasa berstatus 200, bukan redirect atau 403.
     */
    public function store(Request $request)
    {
        // Dibaca dari config (bukan env) supaya tetap aktif setelah config:cache di production
        $secretKey = config('services.recaptcha.secret_key');

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:'.self::MAX_MESSAGE_LENGTH,
        ];
        if ($secretKey) {
            $rules['recaptcha-response'] = 'required|string';
        }

        $validator = Validator::make($request->all(), $rules, [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama terlalu panjang (maksimal 255 karakter).',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Alamat email tidak valid.',
            'email.max' => 'Alamat email terlalu panjang.',
            'subject.required' => 'Judul pertanyaan wajib diisi.',
            'subject.max' => 'Judul pertanyaan terlalu panjang (maksimal 255 karakter).',
            'message.required' => 'Detail pertanyaan wajib diisi.',
            'message.max' => 'Pesan terlalu panjang (maksimal '.self::MAX_MESSAGE_LENGTH.' karakter).',
            'recaptcha-response.required' => 'Gagal memverifikasi reCAPTCHA. Silakan muat ulang halaman.',
        ]);

        if ($validator->fails()) {
            return $this->plain($validator->errors()->first());
        }

        if ($secretKey && ($problem = $this->recaptchaProblem($request))) {
            return $this->plain($problem);
        }

        // Hanya isian form; kolom status (is_read, is_replied, replied_at) diatur admin, bukan pengirim
        ContactMessage::create($validator->safe()->only(['name', 'email', 'subject', 'message']));

        return $this->plain('OK');
    }

    /**
     * Null jika lolos; selain itu pesan untuk pengunjung. Gagal tertutup: bila Google tidak bisa dihubungi
     * atau jawabannya tidak dikenali, pesan tidak disimpan. Logika verifikasi ada di App\Services\Recaptcha.
     */
    private function recaptchaProblem(Request $request): ?string
    {
        $failure = Recaptcha::failure($request->input('recaptcha-response'), config('services.recaptcha.action'), $request->ip());

        return match ($failure) {
            null => null,
            'unreachable' => 'Verifikasi anti-spam sedang tidak bisa dihubungi. Silakan coba lagi beberapa saat lagi.',
            default => self::SPAM_MESSAGE,
        };
    }

    private function plain(string $text): Response
    {
        return response($text, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
