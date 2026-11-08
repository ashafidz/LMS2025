<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Sorting for small in-memory tables (instructor quiz results, assignment submissions).
 *
 * The column and direction come from the query string (?sort=nilai&dir=desc) and are checked against a whitelist.
 * Empty values (null or '') always go last, whatever the direction, so "belum ada nilai" never jumps to the top.
 */
class TableSort
{
    /**
     * @param  array<string, callable>  $columns  sort key => fn ($item) returning the value to compare
     * @return array{0: string, 1: string} [sort key, direction]
     */
    public static function current(array $columns, string $defaultSort, string $defaultDir = 'asc'): array
    {
        $sort = (string) request('sort', $defaultSort);
        $dir = strtolower((string) request('dir', $defaultDir));

        if (! array_key_exists($sort, $columns)) {
            $sort = $defaultSort;
        }
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = $defaultDir;
        }

        return [$sort, $dir];
    }

    /**
     * @param  array<string, callable>  $columns
     */
    public static function apply(Collection $items, array $columns, string $sort, string $dir): Collection
    {
        $resolve = $columns[$sort];

        return $items->sort(function ($a, $b) use ($resolve, $dir) {
            $va = static::normalize($resolve($a));
            $vb = static::normalize($resolve($b));

            if ($va === null || $vb === null) {
                // Empty values last in both directions.
                return ($va === null) <=> ($vb === null);
            }

            $cmp = (is_string($va) && is_string($vb)) ? strnatcasecmp($va, $vb) : $va <=> $vb;

            return $dir === 'desc' ? -$cmp : $cmp;
        })->values();
    }

    private static function normalize(mixed $value): string|int|float|null
    {
        if ($value instanceof CarbonInterface) {
            return $value->getTimestamp();
        }
        if ($value === null || $value === '') {
            return null;
        }
        if (is_bool($value)) {
            return (int) $value;
        }

        return is_numeric($value) && ! is_string($value) ? $value : (string) $value;
    }
}
