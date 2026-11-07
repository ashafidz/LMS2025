<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginLogoutToastTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_flashes_a_welcome_toast_that_the_next_page_shows(): void
    {
        $student = $this->makeStudent(['name' => 'Budi Santoso', 'password' => bcrypt('rahasia-123')]);

        $this->post(route('login'), [
            'email' => $student->email,
            'password' => 'rahasia-123',
            'login_preference' => 'student',
        ])->assertRedirect(route('student.dashboard'))
            ->assertSessionHas('rs_toast', 'Berhasil masuk. Selamat datang, Budi!');

        $this->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('rsToast(', false)
            ->assertSee('Selamat datang, Budi!', false);
    }

    public function test_logout_flashes_a_toast_that_the_home_page_shows(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($student)
            ->post(route('logout'))
            ->assertRedirect('/')
            ->assertSessionHas('rs_toast', 'Anda sudah keluar. Sampai jumpa lagi!');

        $this->get('/')
            ->assertOk()
            ->assertSee('rsToast(', false);
    }
}
