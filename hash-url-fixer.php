#!/usr/bin/env php
<?php

/**
 * Script untuk otomatis replace ->id dengan model object di Blade views
 * Usage: php hash-url-fixer.php
 */

// Models yang sudah pakai HasHashedRouteKey trait
$hashedModels = [
    'course', 'module', 'lesson', 'quiz', 'question', 'topic',
    'attempt', 'certificate', 'order', 'coupon', 'badge',
    'assignment', 'submission', 'category', 'video', 'article',
    'document', 'discussion', 'linkCollection', 'item'
];

// Pattern untuk mencari route dengan ->id
$pattern = '/route\([\'"]([^\'"]+)[\'"],\s*\$([a-zA-Z_]+)->id\)/';

// Directory views
$viewsDir = __DIR__ . '/resources/views';

function scanDirectory($dir, $pattern, $hashedModels) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );
    
    $replacements = [];
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $newContent = $content;
            $changed = false;
            
            // Find all route(..., $model->id) patterns
            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $fullMatch = $match[0];
                    $routeName = $match[1];
                    $modelVar = $match[2];
                    
                    // Check if model is in hashed models list
                    $modelNameLower = strtolower($modelVar);
                    $isHashedModel = false;
                    
                    foreach ($hashedModels as $hashedModel) {
                        if (strpos($modelNameLower, strtolower($hashedModel)) !== false) {
                            $isHashedModel = true;
                            break;
                        }
                    }
                    
                    if ($isHashedModel) {
                        // Replace ->id with model object
                        $replacement = "route('$routeName', \$$modelVar)";
                        $newContent = str_replace($fullMatch, $replacement, $newContent);
                        $changed = true;
                        
                        $replacements[] = [
                            'file' => str_replace(__DIR__ . '/', '', $file->getPathname()),
                            'old' => $fullMatch,
                            'new' => $replacement
                        ];
                    }
                }
            }
            
            // Write if changed
            if ($changed) {
                file_put_contents($file->getPathname(), $newContent);
            }
        }
    }
    
    return $replacements;
}

echo "🔍 Scanning views directory...\n\n";

$replacements = scanDirectory($viewsDir, $pattern, $hashedModels);

if (empty($replacements)) {
    echo "✅ All URLs already using model objects!\n";
} else {
    echo "✨ Fixed " . count($replacements) . " URLs:\n\n";
    
    foreach ($replacements as $i => $replacement) {
        echo ($i + 1) . ". " . $replacement['file'] . "\n";
        echo "   ❌ " . $replacement['old'] . "\n";
        echo "   ✅ " . $replacement['new'] . "\n\n";
    }
    
    echo "\n✅ Done! All URLs now using hash.\n";
}
