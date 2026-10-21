<?php

namespace Tests\Feature\Performance;

use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class QueuedWorkTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_is_queued(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => null]);

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, QueuedVerifyEmail::class);
        $this->assertInstanceOf(ShouldQueue::class, new QueuedVerifyEmail);
    }

    public function test_forgot_password_sends_queued_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasNoErrors();

        Notification::assertSentTo($user, QueuedResetPassword::class);
        $this->assertInstanceOf(ShouldQueue::class, new QueuedResetPassword('token'));
    }

    public function test_scheduler_drains_the_queue_every_minute(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command, 'queue:work') && str_contains($event->command, '--stop-when-empty'));

        $this->assertCount(1, $events);
        $this->assertSame('* * * * *', $events->first()->expression);
    }

    public function test_views_receive_site_settings_without_boot_time_share(): void
    {
        SiteSetting::query()->delete();
        $setting = SiteSetting::forceCreate(['site_name' => 'Ruang Studi Test']);

        $html = view()->file($this->viewFile('{{ $siteSettings->site_name }}'))->render();

        $this->assertStringContainsString($setting->site_name, $html);
    }

    private function viewFile(string $contents): string
    {
        $path = storage_path('framework/testing/site-settings-probe.blade.php');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, $contents);

        return $path;
    }
}
