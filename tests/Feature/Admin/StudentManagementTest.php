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

        // Default page size is 25 now; per_page=10 gives two pages.
        $this->actingAs($admin)
            ->get(route('admin.manajemen-student.index'))
            ->assertOk()
            ->assertSee('Menampilkan <strong>1-12</strong> dari <strong>12</strong> data', false)
            ->assertDontSee('page=2', false);

        $this->actingAs($admin)
            ->get(route('admin.manajemen-student.index', ['per_page' => 10]))
            ->assertOk()
            ->assertSee('Menampilkan <strong>1-10</strong> dari <strong>12</strong> data', false)
            ->assertSee('page=2', false);

        $this->actingAs($admin)
            ->get(route('admin.manajemen-student.index', ['search' => 'Mahasiswa', 'per_page' => 10, 'page' => 2]))
            ->assertOk()
            ->assertSee('Menampilkan <strong>11-12</strong> dari <strong>12</strong> data', false)
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
