<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name', // Ditambahkan
        'company_name',
        'address',
        'phone',
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
    ];

    protected $casts = [
        'vat_percentage' => 'decimal:2',
        'transaction_fee_fixed' => 'decimal:2',
        'transaction_fee_percentage' => 'decimal:2',
    ];
}
