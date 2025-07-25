<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'site_name' => 'LMS Hebat', // Ditambahkan
                'company_name' => 'Nama Perusahaan Anda',
                'address' => 'Alamat Lengkap Perusahaan Anda',
                'phone' => '08123456789',
                'npwp' => '00.000.000.0-000.000',
                'logo_path' => null,
                'vat_percentage' => 11.00,
                'transaction_fee_fixed' => 0,
                'transaction_fee_percentage' => 0,
            ]
        );
    }
}
