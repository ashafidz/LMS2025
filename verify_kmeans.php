<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\KmeansRun;
use App\Services\KMeansService;

$course = Course::find(13); 
$service = app(KMeansService::class);
$data = $service->buildFeatureMatrix($course);

echo "Feature Matrix (Raw Data):\n";
print_r($data['matrix']);

echo "\nAttempt IDs:\n";
print_r($data['attempt_ids']);

$run = KmeansRun::where('course_id', 13)->latest()->first();
echo "\nScatter Plot Coordinates (from JSON):\n";
echo json_encode($run->result_summary['scatter_data'] ?? [], JSON_PRETTY_PRINT);
