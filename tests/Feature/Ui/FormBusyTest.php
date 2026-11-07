<?php

namespace Tests\Feature\Ui;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormBusyTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_loads_the_busy_form_script_after_the_dialog_script(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('js/rs-forms.js', $html);
        $this->assertGreaterThan(strpos($html, 'js/rs-dialog.js'), strpos($html, 'js/rs-forms.js'));
    }

    public function test_dashboard_layout_loads_the_busy_form_script_after_the_dialog_script(): void
    {
        $html = $this->actingAs($this->makeStudent())->get(route('student.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('js/rs-forms.js', $html);
        $this->assertGreaterThan(strpos($html, 'js/rs-dialog.js'), strpos($html, 'js/rs-forms.js'));
    }

    public function test_script_file_exists_and_skips_get_forms_and_opt_outs(): void
    {
        $js = file_get_contents(public_path('js/rs-forms.js'));

        $this->assertStringContainsString('data-no-busy', $js);
        $this->assertStringContainsString("'get'", $js);
        $this->assertStringContainsString('rsFormIdle', $js);
    }
}
