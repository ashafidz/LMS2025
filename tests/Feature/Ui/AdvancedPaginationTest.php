<?php

namespace Tests\Feature\Ui;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AdvancedPaginationTest extends TestCase
{
    private function render(int $total, int $page, array $query = []): string
    {
        $this->app->instance('request', Request::create('/daftar', 'GET', array_merge($query, ['page' => $page])));
        $items = new LengthAwarePaginator(array_fill(0, min(25, $total), 'x'), $total, 25, $page, ['path' => url('/daftar')]);
        $items->appends($query);

        return Blade::render('<x-dash.pagination :items="$items" />', ['items' => $items]);
    }

    public function test_middle_page_shows_info_numbers_with_ellipsis_first_last_and_jump(): void
    {
        $html = $this->render(400, 8, ['search' => 'budi', 'sort' => 'nilai']);
        $flat = str_replace('&amp;', '&', $html);

        $this->assertStringContainsString('Menampilkan <strong>176-200</strong> dari <strong>400</strong> data', $html);
        // Laravel's window: first two, current +-2, last two.
        foreach ([1, 2, 6, 7, 9, 10, 15, 16] as $p) {
            $this->assertMatchesRegularExpression('#href="[^"]*page='.$p.'"[^>]*aria-label="Halaman '.$p.'"#', $flat, "link to page $p");
        }
        $this->assertDoesNotMatchRegularExpression('#aria-label="Halaman (3|4|5|11|12|13|14)"#', $html);
        $this->assertSame(2, substr_count($html, 'rs-pager__gap'));
        $this->assertStringContainsString('aria-current="page">8<', $html);
        $this->assertStringContainsString('aria-label="Halaman pertama"', $html);
        $this->assertStringContainsString('aria-label="Halaman terakhir"', $html);
        $this->assertStringContainsString('Hal 8 dari 16', $html);
        $this->assertStringContainsString('name="page" min="1" max="16"', $html);

        // Every page link keeps the search and the sort.
        preg_match_all('#href="([^"]*page=\d+[^"]*)"#', $flat, $m);
        $this->assertNotEmpty($m[1]);
        foreach ($m[1] as $href) {
            $this->assertStringContainsString('search=budi', $href);
            $this->assertStringContainsString('sort=nilai', $href);
        }
        // The page-size and jump forms keep them as hidden inputs.
        $this->assertStringContainsString('<input type="hidden" name="search" value="budi">', $html);
        $this->assertStringContainsString('name="per_page"', $html);
    }

    public function test_first_page_disables_first_and_previous(): void
    {
        $html = $this->render(400, 1);

        $this->assertSame(2, substr_count($html, 'aria-disabled="true"'));
        $this->assertStringNotContainsString('aria-label="Halaman sebelumnya"', $html);
        $this->assertStringContainsString('aria-label="Halaman berikutnya"', $html);
    }

    public function test_single_page_shows_the_count_without_navigation(): void
    {
        $html = $this->render(8, 1);

        $this->assertStringContainsString('Menampilkan <strong>1-8</strong> dari <strong>8</strong> data', $html);
        $this->assertStringNotContainsString('Navigasi halaman', $html);
        $this->assertStringNotContainsString('name="per_page"', $html); // 8 rows: page size choice is pointless
    }

    public function test_empty_list_says_so(): void
    {
        $this->assertStringContainsString('Tidak ada data', $this->render(0, 1));
    }
}
