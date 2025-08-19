<?php

namespace App\Models;

use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SiteSetting extends Model
{
    use HasFactory;
    use HasLocalDates;

    protected $fillable = [
        'site_name', // Ditambahkan
        'company_name',
        'address',
        'phone',
        'email', // Tambahkan ini
        'facebook_url', // Tambahkan ini
        'twitter_url', // Tambahkan ini
        'instagram_url', // Tambahkan ini
        'youtube_url', // Tambahkan ini
        'npwp',
        'logo_path',
        'vat_percentage',
        'transaction_fee_fixed',
        'transaction_fee_percentage',
        'points_for_purchase',
        'points_for_article',
        'points_for_video',
        'points_for_document',
        'points_for_quiz',
        'points_for_assignment',
        'point_to_diamond_rate',
    ];

    protected $casts = [
        'vat_percentage' => 'decimal:2',
        'transaction_fee_fixed' => 'decimal:2',
        'transaction_fee_percentage' => 'decimal:2',
    ];
}
