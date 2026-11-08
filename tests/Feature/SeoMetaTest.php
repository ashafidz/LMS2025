<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_IMAGE = 'images/og/ruang-studi-share.png';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        SiteSetting::create(['site_name' => 'Ruang Studi Uji']);
        cache()->forget('site_settings');
    }

    public function test_home_page_sends_share_tags_with_the_default_image(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('property="og:title" content="Ruang Studi Uji"', false);
        $response->assertSee('name="twitter:card" content="summary_large_image"', false);
        $response->assertSee('property="og:image:width" content="1200"', false);
        $response->assertDontSee('name="keywords"', false);

        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/name="description" content="[^"]+"/', $html);
        $this->assertMatchesRegularExpression('#property="og:image" content="https?://[^"]+'.preg_quote(self::DEFAULT_IMAGE, '#').'"#', $html);
        $this->assertMatchesRegularExpression('#name="twitter:image" content="https?://[^"]+'.preg_quote(self::DEFAULT_IMAGE, '#').'"#', $html);
    }

    public function test_canonical_url_drops_the_query_string(): void
    {
        $this->get('/courses?page=2&search=abc')
            ->assertOk()
            ->assertSee('rel="canonical" href="'.url('/courses').'"', false);
    }

    public function test_about_page_has_its_own_description_and_title(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('property="og:title" content="Tentang Kami"', false)
            ->assertSee('name="description" content="Kenali Ruang Studi: platform belajar online', false);
    }

    public function test_every_public_page_has_a_description_of_at_most_160_characters(): void
    {
        foreach (['/', '/courses', '/about', '/contact', '/faqs', '/testimonials', '/detail-service', '/verifikasi-sertifikat'] as $url) {
            $response = $this->get($url);
            if ($response->status() !== 200) {
                continue;
            }
            preg_match('/name="description" content="([^"]*)"/', $response->getContent(), $match);
            $this->assertNotEmpty($match[1] ?? '', "No description on $url");
            $this->assertLessThanOrEqual(160, mb_strlen(html_entity_decode($match[1])), "Description too long on $url");
        }
    }

    public function test_course_page_uses_course_title_plain_description_and_thumbnail(): void
    {
        $course = Course::factory()->create([
            'title' => 'Laravel Untuk Pemula',
            'description' => '<p>Belajar <b>Laravel</b> "dasar"</p>',
            'thumbnail_url' => 'course-thumbnails/laravel-pemula.jpg',
        ]);

        $response = $this->get(route('courses.show', $course->slug))->assertOk();

        $response->assertSee('property="og:title" content="Laravel Untuk Pemula"', false);
        $response->assertSee('name="description" content="Belajar Laravel &quot;dasar&quot;"', false);
        $response->assertDontSee('name="description" content="<p>', false);
        $response->assertDontSee('og:image:width', false);

        $this->assertMatchesRegularExpression(
            '#property="og:image" content="https?://[^"]+course-thumbnails/laravel-pemula\.jpg"#',
            $response->getContent()
        );
    }

    public function test_course_description_entities_from_the_editor_become_plain_text(): void
    {
        $course = Course::factory()->create([
            'description' => '<p>Tom&nbsp;&amp; Jerry&#39;s&nbsp; &lt;kelas&gt;</p>',
        ]);

        $this->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee('name="description" content="Tom &amp; Jerry&#039;s &lt;kelas&gt;"', false);
    }

    public function test_course_without_thumbnail_falls_back_to_the_default_image(): void
    {
        $course = Course::factory()->create(['thumbnail_url' => null]);

        $this->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee(self::DEFAULT_IMAGE, false)
            ->assertSee('property="og:image:width" content="1200"', false);
    }

    public function test_course_with_empty_description_uses_the_layout_default(): void
    {
        $course = Course::factory()->create(['description' => '<p>  </p>']);

        $this->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee('name="description" content="Ruang Studi adalah platform belajar online', false);
    }

    public function test_share_image_exists_with_the_expected_size(): void
    {
        $path = public_path(self::DEFAULT_IMAGE);

        $this->assertFileExists($path);
        $size = getimagesize($path);
        $this->assertSame([1200, 630], [$size[0], $size[1]]);
        $this->assertLessThan(300 * 1000, filesize($path));
    }
}
