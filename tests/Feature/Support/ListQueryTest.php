<?php

namespace Tests\Feature\Support;

use App\Models\User;
use App\Support\ListQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ListQueryTest extends TestCase
{
    use RefreshDatabase;

    private function withQuery(array $query): void
    {
        $this->app->instance('request', Request::create('/daftar', 'GET', $query));
    }

    public function test_per_page_accepts_only_the_allowed_sizes(): void
    {
        $this->withQuery(['per_page' => '50']);
        $this->assertSame(50, ListQuery::perPage());

        foreach (['7', '1000', 'abc', '-10', ''] as $bad) {
            $this->withQuery(['per_page' => $bad]);
            $this->assertSame(25, ListQuery::perPage(), "per_page=$bad");
        }

        $this->withQuery(['per_page' => '18']);
        $this->assertSame(18, ListQuery::perPage(9, [9, 18, 36]));
    }

    public function test_search_is_trimmed_and_capped(): void
    {
        $this->withQuery(['search' => '  budi  ']);
        $this->assertSame('budi', ListQuery::search());

        $this->withQuery(['search' => str_repeat('a', 150)]);
        $this->assertSame(100, mb_strlen(ListQuery::search()));
    }

    public function test_apply_search_matches_columns_and_relations(): void
    {
        User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@example.test']);
        User::factory()->create(['name' => 'Ani Lestari', 'email' => 'ani@example.test']);

        $this->withQuery(['search' => 'santo']);
        $this->assertSame(['Budi Santoso'], ListQuery::applySearch(User::query(), ['name', 'email'])->pluck('name')->all());

        $this->withQuery(['search' => 'ANI@EXAMPLE']);
        $this->assertSame(['Ani Lestari'], ListQuery::applySearch(User::query(), ['name', 'email'])->pluck('name')->all());

        $this->withQuery([]);
        $this->assertCount(2, ListQuery::applySearch(User::query(), ['name'])->get());
    }

    public function test_paginate_collection_slices_and_keeps_the_query_string(): void
    {
        $this->withQuery(['page' => '2', 'search' => 'x', 'sort' => 'nilai']);
        $page = ListQuery::paginateCollection(collect(range(1, 60)), 25);

        $this->assertSame(range(26, 50), $page->items());
        $this->assertSame(60, $page->total());
        $this->assertSame(3, $page->lastPage());
        $this->assertStringContainsString('search=x', $page->url(3));
        $this->assertStringContainsString('sort=nilai', $page->url(3));
    }

    public function test_paginate_collection_clamps_a_page_past_the_end_and_uses_its_page_name(): void
    {
        $this->withQuery(['page_passed' => '99']);
        $page = ListQuery::paginateCollection(collect(range(1, 30)), 25, 'page_passed');

        $this->assertSame(2, $page->currentPage());
        $this->assertSame([26, 27, 28, 29, 30], $page->items());
        $this->assertStringContainsString('page_passed=1', $page->url(1));
    }
}
