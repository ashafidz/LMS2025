<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Browser pop-ups (confirm, alert, prompt) are replaced by the site dialog (public/js/rs-dialog.js).
 */
class NoNativeDialogsTest extends TestCase
{
    /** Unused files: no route or view references them. */
    private const SKIP = [
        'student/quizzes/takeStable.blade.php',
        'instructor/question_bank/questions/indexStable.blade.php',
    ];

    public function test_no_view_or_script_calls_a_native_browser_dialog(): void
    {
        $files = [];
        foreach (File::allFiles(resource_path('views')) as $file) {
            if (str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }
        foreach (File::glob(public_path('js/*.js')) as $path) {
            if (is_file($path) && ! str_ends_with($path, '.min.js') && basename($path) !== 'rs-dialog.js') {
                $files[] = $path;
            }
        }

        $found = [];
        foreach ($files as $path) {
            $relative = str_replace('\\', '/', $path);
            foreach (self::SKIP as $skip) {
                if (str_ends_with($relative, $skip)) {
                    continue 2;
                }
            }

            $code = File::get($path);
            // Blank out comments but keep newlines so line numbers stay right.
            $code = preg_replace_callback('/\{\{--.*?--\}\}/s', fn ($m) => str_repeat("\n", substr_count($m[0], "\n")), $code);
            $code = preg_replace('/^\s*\/\/.*$/m', '', $code);

            foreach (explode("\n", $code) as $i => $line) {
                if (preg_match('/(?<![\w.$])(?:window\.)?(confirm|alert|prompt)\s*\(/', $line)) {
                    $found[] = str_replace(base_path().'/', '', $relative).':'.($i + 1);
                }
            }
        }

        $this->assertSame([], $found, "Native dialog found (use rsConfirm/rsAlert or data-confirm):\n".implode("\n", $found));
    }
}
