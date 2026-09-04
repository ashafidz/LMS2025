<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ];

        // Hanya wajibkan reCAPTCHA jika sudah dikonfigurasi di .env
        $secretKey = env('RECAPTCHA_SECRET_KEY');
        if ($secretKey) {
            $rules['recaptcha-response'] = 'required|string';
        }

        $request->validate($rules, [
            'recaptcha-response.required' => 'Gagal memverifikasi reCAPTCHA. Silakan muat ulang halaman.'
        ]);

        if ($secretKey) {
            // Verifikasi reCAPTCHA ke Google
            $recaptchaResponse = $request->input('recaptcha-response');
            
            $response = \Illuminate\Support\Facades\Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => $request->ip()
            ]);

            $result = $response->json();

            if (!$result['success'] || $result['score'] < 0.5) {
                return response('Aktivitas Anda terdeteksi sebagai SPAM oleh Google reCAPTCHA.', 403)->header('Content-Type', 'text/plain');
            }
        }

        \App\Models\ContactMessage::create($request->all());

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }
}
