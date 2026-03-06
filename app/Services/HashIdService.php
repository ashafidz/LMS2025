<?php

namespace App\Services;

use Vinkla\Hashids\Facades\Hashids;

class HashIdService
{
    /**
     * Encode ID menjadi hash string
     */
    public static function encode($id): string
    {
        if (empty($id)) {
            return '';
        }
        
        return Hashids::encode($id);
    }

    /**
     * Decode hash string menjadi ID
     * Support both hash dan integer untuk backward compatibility
     */
    public static function decode($value): ?int
    {
        // Jika kosong, return null
        if (empty($value)) {
            return null;
        }

        // Jika sudah integer, langsung return (backward compatibility)
        if (is_numeric($value)) {
            return (int) $value;
        }

        // Coba decode hash
        $decoded = Hashids::decode($value);
        
        // Jika decode gagal atau kosong, return null
        if (empty($decoded)) {
            return null;
        }

        return $decoded[0] ?? null;
    }

    /**
     * Check apakah value adalah hash atau integer
     */
    public static function isHash($value): bool
    {
        return !is_numeric($value);
    }
}
