<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Form kontak publik. Skrip template (php-email-form/validate.js) hanya menampilkan isi respons bila statusnya 200:
 * "OK" berarti terkirim, teks lain ditampilkan sebagai pesan error. Jadi error untuk pengunjung dikirim sebagai teks 200.
 */
class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Budi',
            'email' => 'budi@example.test',
            'subject' => 'Tanya kursus',
            'message' => 'Apakah ada kelas akhir pekan?',
        ], $override);
    }

    private function withRecaptcha(): void
    {
        config(['services.recaptcha.secret_key' => 'rahasia', 'services.recaptcha.site_key' => 'KUNCI-SITUS']);
    }

    public function test_only_form_fields_are_stored_not_admin_status_fields(): void
    {
        $this->post(route('contact.store'), $this->payload([
            'is_read' => 1, 'is_replied' => 1, 'replied_at' => '2026-01-01 00:00:00',
        ]))->assertOk()->assertSeeText('OK');

        $message = ContactMessage::firstOrFail();
        $this->assertFalse((bool) $message->is_read);
        $this->assertFalse((bool) $message->is_replied);
        $this->assertNull($message->replied_at);
    }

    public function test_too_long_message_is_rejected_with_a_short_plain_text_message(): void
    {
        $response = $this->post(route('contact.store'), $this->payload(['message' => str_repeat('a', 5001)]));

        $response->assertOk();
        $this->assertSame('Pesan terlalu panjang (maksimal 5000 karakter).', $response->getContent());
        $this->assertStringStartsWith('text/plain', $response->headers->get('Content-Type'));
        $this->assertSame(0, ContactMessage::count());
    }

    public function test_invalid_email_gets_a_plain_message_instead_of_a_redirected_page(): void
    {
        $response = $this->post(route('contact.store'), $this->payload(['email' => 'bukan-email']));

        $response->assertOk();
        $this->assertSame('Alamat email tidak valid.', $response->getContent());
    }

    public function test_recaptcha_secret_is_read_from_config_so_it_works_with_config_cache(): void
    {
        $this->withRecaptcha();
        Http::fake(['www.google.com/*' => Http::response(['success' => true, 'score' => 0.9, 'action' => 'php_email_form_submit'])]);

        $this->post(route('contact.store'), $this->payload(['recaptcha-response' => 'token']))->assertOk()->assertSeeText('OK');

        Http::assertSent(fn ($request) => $request['secret'] === 'rahasia' && $request['response'] === 'token');
        $this->assertSame(1, ContactMessage::count());
    }

    public function test_missing_token_is_rejected_when_recaptcha_is_configured(): void
    {
        $this->withRecaptcha();
        Http::fake();

        $response = $this->post(route('contact.store'), $this->payload());

        $this->assertSame('Gagal memverifikasi reCAPTCHA. Silakan muat ulang halaman.', $response->getContent());
        $this->assertSame(0, ContactMessage::count());
        Http::assertNothingSent();
    }

    public function test_low_score_or_wrong_action_is_treated_as_spam(): void
    {
        $this->withRecaptcha();

        foreach ([
            ['success' => true, 'score' => 0.2, 'action' => 'php_email_form_submit'],
            ['success' => true, 'score' => 0.9, 'action' => 'login'],
            ['success' => false, 'error-codes' => ['invalid-input-response']],
        ] as $googleAnswer) {
            Http::fake(['www.google.com/*' => Http::response($googleAnswer)]);

            $response = $this->post(route('contact.store'), $this->payload(['recaptcha-response' => 'token']));

            $this->assertSame('Pesan tidak terkirim karena terdeteksi sebagai spam. Jika Anda bukan robot, coba lagi beberapa saat lagi.', $response->getContent());
        }

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_google_unreachable_or_odd_answer_does_not_cause_a_server_error(): void
    {
        $this->withRecaptcha();

        foreach ([
            fn () => throw new ConnectionException('timeout'),
            fn () => Http::response('bukan json', 200),
            fn () => Http::response(['success' => true], 200), // tanpa score dan action (kunci v2)
            fn () => Http::response('', 503),
        ] as $i => $answer) {
            Http::fake(['www.google.com/*' => $answer]);

            $response = $this->post(route('contact.store'), $this->payload(['recaptcha-response' => 'token']));

            $response->assertOk();
            $this->assertNotSame('OK', $response->getContent(), "kasus $i");
        }

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_without_recaptcha_configured_google_is_not_called(): void
    {
        config(['services.recaptcha.secret_key' => null]);
        Http::fake();

        $this->post(route('contact.store'), $this->payload())->assertSeeText('OK');

        Http::assertNothingSent();
        $this->assertSame(1, ContactMessage::count());
    }

    public function test_contact_page_reads_the_site_key_from_config(): void
    {
        $this->withRecaptcha();

        $this->get(route('contact'))->assertOk()
            ->assertSee('data-recaptcha-site-key="KUNCI-SITUS"', false)
            ->assertSee('recaptcha/api.js?render=KUNCI-SITUS', false);
    }
}
