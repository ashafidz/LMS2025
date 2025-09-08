<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run()
    {
        SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'site_name' => 'LMS Hebat',
                'company_name' => 'Nama Perusahaan Anda',
                'address' => 'Alamat Lengkap Perusahaan Anda',
                'phone' => '08123456789',
                'npwp' => '00.000.000.0-000.000',
                'facebook_url' => '#', // Tambahkan ini
                'twitter_url' => '#', // Tambahkan ini
                'instagram_url' => '#', // Tambahkan ini
                'youtube_url' => '#', // Tambahkan ini
                'logo_path' => null,
                'vat_percentage' => 11.00,
                'transaction_fee_fixed' => 0,
                'transaction_fee_percentage' => 0,
                // Tambahkan nilai default untuk poin
                'points_for_purchase' => 100,
                'points_for_article' => 3,
                'points_for_video' => 5,
                'points_for_document' => 3,
                'points_for_quiz' => 15,
                'points_for_assignment' => 20,
                'point_to_diamond_rate' => 1.00,
            ]
        );
    }
}
