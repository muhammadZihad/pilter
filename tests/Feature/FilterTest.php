<?php

use Illuminate\Database\Eloquent\Builder;
use Zihad\Pilter\Filters\Filter;

class TestFilter extends Filter
{
    protected array $filterableFields = ['name', 'email', 'status'];
    protected array $sortableFields = ['name', 'email', 'created_at'];
    protected array $alias = ['username' => 'name'];
    
    protected function search(string $value): void
    {
        $this->getQuery()->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%");
    }
    
    protected function status($value)
    {
        $this->getQuery()->where('status', $value);
    }
    
    protected function sortCreatedAt($order)
    {
        $this->getQuery()->orderBy('created_at', $order);
    }
}

test('it can filter by a field', function () {
    $query = mock(Builder::class);
    $query->shouldReceive('where')->once()->with('name', 'like', '%John%')->andReturnSelf();
    
    $filter = new TestFilter($query, ['name' => 'John']);
    $filter->filter();
});

test('it can filter by alias field', function () {
    $query = mock(Builder::class);
    $query->shouldReceive('where')->once()->with('name', 'like', '%John%')->andReturnSelf();
    
    $filter = new TestFilter($query, ['username' => 'John']);
    $filter->filter();
});

test('it can filter by custom method', function () {
    $query = mock(Builder::class);
    $query->shouldReceive('where')->once()->with('status', 'active')->andReturnSelf();
    
    $filter = new TestFilter($query, ['status' => 'active']);
    $filter->filter();
});

test('it can sort by a field', function () {
    $query = mock(Builder::class);
    $query->shouldReceive('orderBy')->zeroOrMoreTimes()->with('name', 'ASC')->andReturnSelf();
    
    $filter = new TestFilter($query, ['sort' => 'name']);
    $filter->sort();
});

test('it can sort by a field in descending order', function () {
    $query = mock(Builder::class);
    $query->shouldReceive('orderBy')->zeroOrMoreTimes()->with('name', 'DESC')->andReturnSelf();
    
    $filter = new TestFilter($query, ['sort' => '-name']);
    $filter->sort();
});


test('it can sort by custom method', function () {
    $query = mock(Builder::class);
    $query->shouldReceive('orderBy')->once()->with('created_at', 'ASC')->andReturnSelf();
    
    $filter = new TestFilter($query, ['sort' => 'created_at']);
    $filter->sort();
});

test('it can search globally', function () {
    $query = mock(Builder::class);
    $query->shouldReceive('where')->once()->with('name', 'like', '%searchterm%')->andReturnSelf();
    $query->shouldReceive('orWhere')->once()->with('email', 'like', '%searchterm%')->andReturnSelf();
    
    $filter = new TestFilter($query, ['search' => 'searchterm']);
    $filter->filter();
});