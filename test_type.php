<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lesson = \App\Models\Lesson::where('lessonable_type', 'like', '%Wordcloud%')->first();
if ($lesson) {
    echo "Type: " . $lesson->lessonable_type . "\n";
    echo "Base: " . strtolower(class_basename($lesson->lessonable_type)) . "\n";
} else {
    echo "No wordcloud lesson found.\n";
}
