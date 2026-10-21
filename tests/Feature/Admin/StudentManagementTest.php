<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_students_by_name_email_or_id_number(): void
    {
        $admin = $this->makeAdmin();
        $budi = $this->makeStudent(['name' => 'Budi Santoso', 'email' => 'budi@example.test']);
        $this->makeStudent(['name' => 'Siti Aminah', 'email' => 'siti@example.test']);
        $budi->studentProfile()->update(['unique_id_number' => 'NIM-12345']);

        foreach (['budi', 'BUDI@EXAMPLE', '12345'] as $term) {
            $this->actingAs($admin)
                ->get(route('admin.manajemen-student.index', ['search' => $term]))
                ->assertOk()
                ->assertSee('Budi Santoso')
                ->assertDontSee('Siti Aminah');
        }
    }

    public function test_search_without_match_shows_friendly_message(): void
    {
        $admin = $this->makeAdmin();
        $this->makeStudent(['name' => 'Budi Santoso']);

        $this->actingAs($admin)
            ->get(route('admin.manajemen-student.index', ['search' => 'tidakada']))
            ->assertOk()
            ->assertSee('Tidak ada student yang cocok')
            ->assertDontSee('Budi Santoso');
    }

    public function test_list_is_paginated_and_keeps_search_in_page_links(): void
    {
        $admin = $this->makeAdmin();
        for ($i = 1; $i <= 12; $i++) {
            $this->makeStudent(['name' => "Mahasiswa {$i}"]);
        }

        $this->actingAs($admin)
            ->get(route('admin.manajemen-student.index'))
            ->assertOk()
            ->assertSee('Menampilkan 1-10 dari 12 student')
            ->assertSee('page=2', false);

        $this->actingAs($admin)
            ->get(route('admin.manajemen-student.index', ['search' => 'Mahasiswa', 'page' => 2]))
            ->assertOk()
            ->assertSee('Menampilkan 11-12 dari 12 student')
            ->assertSee('search=Mahasiswa', false);
    }

    public function test_superadmin_can_open_student_list(): void
    {
        $this->actingAs($this->makeSuperadmin())
            ->get(route('superadmin.manajemen-student.index', ['search' => 'x']))
            ->assertOk();
    }

    public function test_student_cannot_open_student_list(): void
    {
        $this->actingAs($this->makeStudent())
            ->get(route('admin.manajemen-student.index'))
            ->assertForbidden();
    }
}
