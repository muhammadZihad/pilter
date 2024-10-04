<?php

namespace Zihad\Pilter\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;
use Throwable;

abstract class Filter
{
    protected array $filterableFields = [];
    protected array $sortableFields = [];
    protected array $alias = [];

    /**
     * @var string|null A column name which used be used for mandatory sorting at the end
     */
    protected ?string $mandatorySortColumn = null;
    protected string $mandatorySortOrder = 'asc';

    public function __construct(protected Builder  $query, protected array $filterables)
    {
    }

    /**
     * Apply filter on the query
     *
     * @return self
     */
    public function filter(): self
    {
        $filterables = $this->filterables;
        unset($filterables['sort']);
        $this->initialQuery();
        foreach ($filterables as $method => $value) {
            try {
                if (method_exists($this, $method)) {
                    $this->$method($value);
                } elseif ($this->isFilterable($method)) {
                    $this->columnSearch($this->getAlias($method), $value);
                }
            } catch (Throwable $th) {
                // skip if face any error while building query
            }
        }
        return $this;
    }


    /**
     * Initial query runs by default
     *
     * @return void
     */
    protected function initialQuery()
    {
    }

    /**
     * Checks if the key is avialable for filter
     *
     * @param string $key
     * @return boolean
     */
    protected function isFilterable(string $key): bool
    {
        return in_array($key, $this->getFilterableFields()) || in_array($this->getAlias($key), $this->getFilterableFields());
    }

    protected function columnSearch(string $column, string $value): void
    {
        $this->query->where($column, 'like', '%' . $value . '%');
    }


    public function sort(): self
    {
        $sortingColumns = isset($this->filterables['sort']) && $this->filterables['sort'] != 'NO_SORT' ? $this->filterables['sort'] : $this->defaultSort();

        if (is_null($sortingColumns)) {
            return $this;
        }

        $sortableColumns = array_filter(array_map('trim', explode(',', $sortingColumns)));
        foreach ($sortableColumns as $column) {
            // check the order type
            $orderType = substr($column, 0, 1) == '-' ? "DESC" : "ASC";
            $column = ltrim($column, '-');
            // replace extra characters from column name
            $columnName = $this->getAlias(preg_replace("/[^a-zA-Z0-9_]/", "", $column));

            $method = 'sort' . ucfirst(Str::camel($columnName));

            try {
                if (method_exists($this, $method)) {
                    $this->$method($orderType);
                } elseif ($this->isSortable($columnName)) {
                    $this->sortBy($columnName, $orderType);
                }
            } catch (Throwable $th) {
                // skip if any error occurs
            }
        }
        if ($this->mandatorySortColumn) {
            $this->getQuery()->orderByRaw("{$this->mandatorySortColumn} {$this->mandatorySortOrder}");
        }

        return $this;
    }

    public function getQuery(): Builder
    {
        return $this->query;
    }


    protected function getFilterableFields(): array
    {
        return $this->filterableFields;
    }

    protected function getSortableFields(): array
    {
        return $this->sortableFields;
    }

    protected function isSortable($column): bool
    {
        return in_array($column, $this->getSortableFields());
    }


    protected function sortBy(string $column, string $order): void
    {
        $this->query->orderByRaw("$column $order");
    }

    /**
     * Get alias name of the column
     *
     * @param string $alias
     * @return string
     */
    protected function getAlias(string $alias): string
    {
        return $this->alias[$alias] ?? $alias;
    }

    protected function search(string $value): void
    {
        $searchables = $this->filterableFields;
        $this->getQuery()->where(function ($subquery) use ($searchables, $value) {
            foreach ($searchables as $field) {
                $subquery->orWhere($field, 'like', '%' . $value . '%');
            }
        });
    }


    /**
     * Checks if a table is already joined
     *
     * @param string $table
     * @return boolean
     */
    protected function isJoined(string $table): bool
    {
        $query = $this->getQuery()->getQuery();

        if ($query instanceof EloquentBuilder) {
            $query = $query->getQuery();
        }

        return SupportCollection::make($query->joins)->pluck('table')->contains($table);
    }


    /**
     * Get the default sort from the config file
     *
     * @return string|null
     */
    protected function defaultSort(): string|null
    {
        return config('pilter.default_sort');
    }


    public function __call($method, $parameters)
    {
        return $this->filter()->sort()->getQuery()->$method(...$parameters);
    }
}
