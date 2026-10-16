<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateFreePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        SiteSetting::create(['site_name' => 'Ruang Studi Uji']);
        cache()->forget('site_settings');
    }

    public function test_pages_do_not_load_template_assets(): void
    {
        $urls = ['/', '/courses', '/about', '/contact', '/faqs', '/testimonials', '/detail-service', '/login', '/register', '/forgot-password'];

        foreach ($urls as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('ruang-studi.css', false)
                ->assertSee('favicon.svg', false)
                ->assertDontSee('home-page/', false)
                ->assertDontSee('main.css', false)
                ->assertDontSee('bootstrap.min.css', false)
                ->assertDontSee('bootstrap.bundle', false)
                ->assertDontSee('BootstrapMade', false)
                ->assertDontSee('pcoded', false);
        }
    }

    public function test_unknown_url_shows_plain_not_found_page(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('class="rs-body rs-plain"', false)
            ->assertDontSee('Permintaan', false)
            ->assertDontSee('domainesia', false);
    }

    public function test_login_form_keeps_fields(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('action="'.route('login').'"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="login_preference" value="student"', false)
            ->assertSee('name="login_preference" value="instructor"', false)
            ->assertSee('name="timezone"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="remember"', false)
            ->assertSee(route('password.request'), false)
            ->assertSee(route('register'), false);
    }

    public function test_login_as_approved_instructor_still_reaches_instructor_dashboard(): void
    {
        // makeInstructor() creates a verified user with an approved profile; the factory password is "password".
        $instructor = $this->makeInstructor();

        $this->post('/login', [
            'email' => $instructor->email,
            'password' => 'password',
            'login_preference' => 'instructor',
            'timezone' => 'Asia/Jakarta',
        ])->assertRedirect(route('instructor.dashboard'));

        $this->assertSame('Asia/Jakarta', $instructor->refresh()->timezone);
    }

    public function test_register_keeps_fields_and_reopens_instructor_choice_after_error(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('name="role"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="headline"', false)
            ->assertSee('name="website_url"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false);

        $this->from('/register')->post('/register', [
            'role' => 'instructor',
            'name' => 'A',
            'email' => 'a@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors(['headline', 'website_url']);

        // old() input is flashed for one request only, so read the page once.
        $response = $this->get('/register')->assertOk();
        $this->assertMatchesRegularExpression('/value="instructor"\s+checked/', $response->getContent());
    }

    public function test_cart_keeps_forms_and_shows_real_vat_and_coupon_label(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create();
        $cartRow = $student->carts()->create(['course_id' => $course->id]);

        SiteSetting::first()->update(['vat_percentage' => 11]);
        cache()->forget('site_settings');

        Coupon::factory()->create([
            'type' => 'fixed',
            'value' => 25000,
            'is_public' => true,
            'is_active' => true,
            'course_id' => null,
        ]);

        $this->actingAs($student)
            ->withSession(['active_role' => 'student', 'error' => 'Pesan uji'])
            ->get('/cart')
            ->assertOk()
            ->assertSee(route('student.cart.remove', $cartRow), false)
            ->assertSee(route('student.cart.apply_coupon'), false)
            ->assertSee(route('checkout.process'), false)
            ->assertSee('name="code"', false)
            ->assertSee('form="checkout-form"', false)
            ->assertSee('PPN (11%)', false)
            ->assertSee('Potongan Rp25.000', false)
            ->assertSee('Pesan uji')
            ->assertDontSee('JAGOCODING')
            ->assertDontSee('data-bs-', false);
    }

    public function test_checkout_summary_lists_tax_and_fee_and_picks_snap_url(): void
    {
        $student = $this->makeStudent();
        $order = Order::factory()->create([
            'user_id' => $student->id,
            'status' => 'pending',
            'snap_token' => 'dummy-snap-token',
            'vat_amount' => 1100,
            'vat_percentage_at_purchase' => 11,
            'transaction_fee_amount' => 500,
            'final_amount' => 101600,
        ]);

        $this->actingAs($student)->withSession(['active_role' => 'student']);

        config(['midtrans.is_production' => false]);
        $this->get(route('checkout.show', $order))
            ->assertOk()
            ->assertSee('Biaya Layanan')
            ->assertSee('PPN (11%)', false)
            ->assertSee('app.sandbox.midtrans.com', false);

        config(['midtrans.is_production' => true]);
        $this->get(route('checkout.show', $order))
            ->assertOk()
            ->assertSee('https://app.midtrans.com/snap/snap.js', false);
    }

    public function test_account_status_page_uses_plain_layout(): void
    {
        $instructor = $this->makeInstructor();
        $instructor->instructorProfile->update(['application_status' => 'pending']);

        $this->actingAs($instructor)
            ->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.pending'))
            ->assertOk()
            ->assertSee('Pendaftaran instruktur sedang ditinjau')
            ->assertSee(route('logout'), false)
            ->assertSee('class="rs-body rs-plain"', false);
    }
}
