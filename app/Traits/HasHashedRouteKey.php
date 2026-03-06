<?php

namespace App\Traits;

use App\Services\HashIdService;

trait HasHashedRouteKey
{
    /**
     * Override getRouteKey untuk return hash secara default
     * Method ini dipanggil Laravel saat generate URL dengan route()
     */
    public function getRouteKey()
    {
        return HashIdService::encode($this->getKey());
    }

    /**
     * Get route key value yang di-hash (alias untuk compatibility)
     */
    public function getHashedRouteKey(): string
    {
        return $this->getRouteKey();
    }

    /**
     * Override route model binding untuk support hash & integer
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Jika field explicitly set (misal: {course:slug}), gunakan parent tanpa decode
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }
        
        // Decode value (support both hash dan integer untuk default ID binding)
        $id = HashIdService::decode($value);
        
        if ($id === null) {
            return null;
        }

        // Use standard resolveRouteBinding dengan ID yang sudah di-decode
        return parent::resolveRouteBinding($id, $field);
    }

    /**
     * Helper untuk generate hashed URL
     */
    public function hashedUrl($route, $parameters = [])
    {
        return route($route, array_merge([$this->getHashedRouteKey()], $parameters));
    }
}
