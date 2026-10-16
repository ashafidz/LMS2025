<?php

namespace Tests\Feature;

use App\Models\InstructorProfile;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardShellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        SiteSetting::create(['site_name' => 'Ruang Studi Uji']);
        cache()->forget('site_settings');
    }

    public function test_every_role_dashboard_uses_the_new_shell(): void
    {
        $cases = [
            [$this->makeInstructor(), 'instructor', 'instructor.dashboard'],
            [$this->makeAdmin(), 'admin', 'admin.dashboard'],
            [$this->makeSuperadmin(), 'superadmin', 'superadmin.dashboard'],
            [$this->makeStudent(), 'student', 'student.dashboard'],
        ];

        foreach ($cases as [$user, $role, $name]) {
            $this->actingAs($user)
                ->withSession(['active_role' => $role])
                ->get(route($name))
                ->assertOk()
                ->assertSee('class="rs-dash"', false)
                ->assertSee('ruang-studi-dashboard.css', false)
                ->assertSee('ruang-studi-dashboard.js', false)
                ->assertSee('action="'.route('logout').'"', false)
                ->assertSee('name="_token"', false)
                ->assertSee('Ruang Studi Uji', false)
                ->assertDontSee('pcoded.min.js', false)
                ->assertDontSee('vertical-layout.min.js', false)
                ->assertDontSee('smooth-loader', false)
                ->assertDontSee('family=Roboto', false)
                ->assertDontSee('class="pcoded-navbar', false);
        }
    }

    public function test_instructor_menu_marks_the_current_section(): void
    {
        $this->actingAs($this->makeInstructor())
            ->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.courses.index'))
            ->assertOk()
            ->assertSee('href="'.route('instructor.courses.index').'" aria-current="page"', false)
            ->assertSee('href="'.route('instructor.dashboard').'" aria-current="false"', false)
            ->assertSee(route('instructor.question-bank.topics.index'), false)
            ->assertSee(route('instructor.courses.create'), false);
    }

    public function test_admin_menu_lists_admin_pages_only(): void
    {
        $response = $this->actingAs($this->makeAdmin())
            ->withSession(['active_role' => 'admin'])
            ->get(route('admin.dashboard'))
            ->assertOk();

        foreach ([
            'admin.dashboard',
            'admin.instructor-application.index',
            'admin.manajemen-student.index',
            'admin.course-enrollments.index',
            'admin.publication.index',
            'admin.course-categories.index',
            'admin.coupons.index',
            'admin.point-sync.index',
            'admin.contact-messages.index',
            'admin.likert-questions.index',
        ] as $name) {
            $response->assertSee('href="'.route($name).'"', false);
        }

        $response->assertDontSee(route('superadmin.settings.edit'), false)
            ->assertDontSee(route('superadmin.admins.index'), false);
    }

    public function test_superadmin_menu_adds_admins_badges_and_settings(): void
    {
        $this->actingAs($this->makeSuperadmin())
            ->withSession(['active_role' => 'superadmin'])
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertSee(route('superadmin.admins.index'), false)
            ->assertSee(route('superadmin.badges.index'), false)
            ->assertSee(route('superadmin.settings.edit'), false)
            ->assertSee('Pengaturan situs', false);
    }

    public function test_student_who_applied_as_instructor_keeps_the_role_switch(): void
    {
        $applicant = $this->makeStudent();
        $applicant->assignRole('instructor');
        InstructorProfile::factory()->create([
            'user_id' => $applicant->id,
            'application_status' => 'pending',
        ]);

        $this->actingAs($applicant)
            ->withSession(['active_role' => 'student'])
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee(route('role.switch', 'instructor'), false);

        $this->actingAs($this->makeStudent())
            ->withSession(['active_role' => 'student'])
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertDontSee('/switch-role/', false);
    }

    public function test_role_switch_only_for_instructors_who_are_also_students(): void
    {
        $both = $this->makeInstructor();
        $both->assignRole('student');

        $this->actingAs($both)
            ->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.dashboard'))
            ->assertOk()
            ->assertSee(route('role.switch', 'student'), false);

        $this->actingAs($this->makeInstructor())
            ->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.dashboard'))
            ->assertOk()
            ->assertDontSee('/switch-role/', false);
    }

    public function test_student_shell_shows_cart_and_learning_menu(): void
    {
        $this->actingAs($this->makeStudent())
            ->withSession(['active_role' => 'student'])
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee(route('student.my_courses'), false)
            ->assertSee(route('student.cart.index'), false)
            ->assertSee('Riwayat transaksi', false)
            ->assertSee('href="'.route('student.dashboard').'" aria-current="page"', false);
    }
}
