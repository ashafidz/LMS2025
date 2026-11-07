<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthCaptchaAndThrottleTest extends TestCase
{
    use RefreshDatabase;

    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        config()->set('services.recaptcha.site_key', null);
        config()->set('services.recaptcha.secret_key', null);
        RateLimiter::clear('register');
    }

    private function withKeys(): void
    {
        config()->set('services.recaptcha.site_key', 'site-key');
        config()->set('services.recaptcha.secret_key', 'secret-key');
    }

    private function registerData(array $overrides = []): array
    {
        return $overrides + [
            'role' => 'student',
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    public function test_without_keys_pages_have_no_captcha_and_forms_work(): void
    {
        Http::fake();
        foreach (['/login', '/register', '/forgot-password'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('recaptcha', false);
        }

        $this->post('/register', $this->registerData());
        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);

        $this->post(route('logout'));
        $student = $this->makeStudent(['password' => bcrypt('rahasia-123')]);
        $this->post(route('login.store'), ['email' => $student->email, 'password' => 'rahasia-123', 'login_preference' => 'student'])
            ->assertRedirect(route('student.dashboard'));
        Http::assertNothingSent();
    }

    public function test_with_keys_pages_carry_the_action_and_the_google_script(): void
    {
        $this->withKeys();

        foreach (['/login' => 'login', '/register' => 'register', '/forgot-password' => 'forgot_password'] as $url => $action) {
            $this->get($url)->assertOk()
                ->assertSee('data-recaptcha-action="'.$action.'"', false)
                ->assertSee('recaptcha/api.js?render=site-key', false)
                ->assertSee('name="recaptcha-response"', false)
                ->assertSee('js/rs-recaptcha.js', false);
        }
    }

    public function test_register_without_token_is_refused_without_calling_google(): void
    {
        $this->withKeys();
        Http::fake();

        $this->from('/register')->post('/register', $this->registerData())
            ->assertRedirect('/register')
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
        Http::assertNothingSent();
    }

    public function test_register_checks_score_and_action(): void
    {
        $this->withKeys();

        Http::fakeSequence(self::VERIFY_URL)
            ->push(['success' => true, 'score' => 0.1, 'action' => 'register'])
            ->push(['success' => true, 'score' => 0.9, 'action' => 'login'])
            ->push(['success' => true, 'score' => 0.9, 'action' => 'register']);

        $this->post('/register', $this->registerData(['recaptcha-response' => 't']))->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);

        $this->post('/register', $this->registerData(['recaptcha-response' => 't']))->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);

        $this->post('/register', $this->registerData(['recaptcha-response' => 't']));
        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }

    public function test_login_fails_open_but_register_fails_closed_when_google_is_unreachable(): void
    {
        $this->withKeys();
        Http::fake(fn () => throw new ConnectionException('down'));

        $student = $this->makeStudent(['password' => bcrypt('rahasia-123')]);
        $this->post(route('login.store'), ['email' => $student->email, 'password' => 'rahasia-123', 'login_preference' => 'student', 'recaptcha-response' => 't'])
            ->assertRedirect(route('student.dashboard'));

        $this->post('/register', $this->registerData(['recaptcha-response' => 't']))->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
    }

    public function test_forgot_password_without_token_sends_nothing(): void
    {
        $this->withKeys();
        Notification::fake();
        $student = $this->makeStudent();

        $this->post('/forgot-password', ['email' => $student->email])->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }

    public function test_register_is_limited_to_five_per_minute_per_ip(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->post('/register', ['email' => "x{$i}@example.com"])->assertStatus(302);
        }
        $this->post('/register', ['email' => 'x6@example.com'])->assertStatus(429);

        $this->withServerVariables(['REMOTE_ADDR' => '10.9.8.7'])
            ->post('/register', ['email' => 'other@example.com'])
            ->assertStatus(302);
    }

    public function test_login_is_limited_to_five_per_minute_per_email_and_ip(): void
    {
        $data = ['email' => 'nobody@example.com', 'password' => 'salah-banget', 'login_preference' => 'student'];
        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('login.store'), $data)->assertStatus(302);
        }

        $this->post(route('login.store'), $data)->assertStatus(429);
        $this->assertNull(User::where('email', 'nobody@example.com')->first());
    }
}
