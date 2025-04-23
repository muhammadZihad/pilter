<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Zihad\Pilter\Filters\Filter;
use Zihad\Pilter\Traits\Filterable;
use Zihad\Pilter\Exceptions\ClassNotFoundException;
use Zihad\Pilter\Exceptions\InvalidFilterClassException;

class TestModel extends Model
{
    use Filterable;
}

class TestModelFilter extends Filter
{
    protected array $filterableFields = ['name', 'email'];
    protected array $sortableFields = ['name', 'created_at'];
    
    protected function search(string $value): void
    {
        $this->getQuery()->where('name', 'like', "%{$value}%");
    }
}

test('it can apply filters through the trait', function () {
    $query = mock(Builder::class);
    $query->shouldReceive('where')->once()->with('name', 'like', '%John%')->andReturnSelf();
    $query->shouldReceive('getQuery')->andReturn($query);
    
    $model = new TestModel();
    $result = $model->scopeApplyFilter($query, ['name' => 'John'], TestModelFilter::class);
    
    expect($result)->toBe($query);
});

test('it throws exception when filter class does not exist', function () {
    $query = mock(Builder::class);
    $model = new TestModel();
    
    expect(fn() => $model->scopeApplyFilter($query, [], 'NonExistentFilter'))
        ->toThrow(ClassNotFoundException::class);
});

test('it throws exception when filter class does not extend Filter', function () {
    $query = mock(Builder::class);
    $model = new TestModel();
    
    expect(fn() => $model->scopeApplyFilter($query, [], TestModel::class))
        ->toThrow(InvalidFilterClassException::class);
});

test('it can apply both filter and sort', function () {
    $query = mock(Builder::class);
    $query->shouldReceive('where')->once()->with('name', 'like', '%John%')->andReturnSelf();
    $query->shouldReceive('orderBy')->zeroOrMoreTimes()->with('name', 'ASC')->andReturnSelf();
    $query->shouldReceive('getQuery')->andReturn($query);
    
    $model = new TestModel();
    $result = $model->scopeApplyFilter($query, [
        'name' => 'John',
        'sort' => 'name'
    ], TestModelFilter::class);
    
    expect($result)->toBe($query);
});