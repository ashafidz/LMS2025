<?php

namespace App\Support;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * Search and page-size helpers for dashboard lists (?search=, ?per_page=).
 *
 * Search is a case-insensitive LIKE on the given columns. A column written as "relation:column" searches through a
 * relation with whereHas (for example "user:name" or "course.instructor:name").
 */
class ListQuery
{
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public const DEFAULT_PER_PAGE = 25;

    /**
     * @param  array<int>|null  $options  allowed sizes; the default must be one of them
     */
    public static function perPage(int $default = self::DEFAULT_PER_PAGE, ?array $options = null): int
    {
        $options ??= self::PER_PAGE_OPTIONS;
        $value = filter_var(request('per_page'), FILTER_VALIDATE_INT);

        return in_array($value, $options, true) ? $value : $default;
    }

    public static function search(): string
    {
        return mb_substr(trim((string) request('search', '')), 0, 100);
    }

    /**
     * @param  array<int, string>  $columns
     */
    public static function applySearch(BuilderContract $query, array $columns): BuilderContract
    {
        $term = self::search();
        if ($term === '' || $columns === []) {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function ($q) use ($columns, $like) {
            foreach ($columns as $column) {
                if (str_contains($column, ':')) {
                    [$relation, $field] = explode(':', $column, 2);
                    $q->orWhereHas($relation, fn ($r) => $r->where($field, 'like', $like));
                } else {
                    $q->orWhere($column, 'like', $like);
                }
            }
        });
    }

    /**
     * Paginate a list built in memory, keeping the query string (search, sort, tab, per_page).
     */
    public static function paginateCollection(Collection $items, int $perPage, string $pageName = 'page'): LengthAwarePaginator
    {
        $page = max(1, (int) Paginator::resolveCurrentPage($pageName));
        $lastPage = max(1, (int) ceil($items->count() / $perPage));
        $page = min($page, $lastPage);

        return (new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => $pageName]
        ))->withQueryString();
    }
}
