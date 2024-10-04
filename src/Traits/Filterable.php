<?php

namespace Zihad\Pilter\Traits;

/**
 * @method static Builder applyFilter(array $filterables, string|null $filterableClass = null)
 */

use Zihad\Pilter\Exceptions\InvalidFilterClassException;
use Zihad\Pilter\Exceptions\ClassNotFoundException;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Zihad\Pilter\Filters\Filter;

trait Filterable
{
    /**
     * Scope for applying query parameter filters
     *
     * @param Builder $query
     * @param array $filterables input array of filterable query
     * @param string|null $filterableClass Filterable class name
     * @return Builder
     * @throws ClassNotFoundException
     * @throws InvalidFilterClassException
     */
    public function scopeApplyFilter(Builder $query, array $filterables, string|null $filterableClass = null)
    {
        if (is_null($filterableClass)) {
            $filterableClass =  'App\\Filters\\' . class_basename($this) . 'Filter';
        }
        if (!class_exists($filterableClass)) {
            throw new ClassNotFoundException(printf('%s class not found.', $filterableClass));
        }
        if (!is_subclass_of($filterableClass, Filter::class)) {
            throw new InvalidFilterClassException(printf(
                '%s is not an instance of %s class. Filterable class must extend %s class',
                $filterableClass, Filter::class, Filter::class
            ));
        }
        return (new $filterableClass($query, $filterables))->filter()->sort()->getQuery();
    }
}
