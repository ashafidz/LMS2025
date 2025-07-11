<?php

namespace Database\Seeders;

use App\Models\CourseCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Web Development',
            'Data Science',
            'Mobile Development',
            'Business & Entrepreneurship',
            'Marketing',
            'Design & Creativity',
            'Personal Development',
            'Health & Fitness',
            'IT & Software',
            'Photography & Video',
        ];

        foreach ($categories as $category) {
            CourseCategory::create([
                'name' => $category,
                'slug' => Str::slug($category),
            ]);
        }
    }
}
