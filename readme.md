# Pilter

Pilter is a Laravel package designed to simplify and organize filter and sorting queries in your Laravel applications. By separating these operations into dedicated classes, Pilter helps make your code cleaner, more readable, and easier to maintain.


# Installation

You can install the package via Composer:

```bash
composer require zihad/pilter
```

## Usage/Examples
Create a new `Filter` class `PostFilter`

`php artisan make:filter Post`

```php
namespace App\Filters;

use Zihad\Pilter\Filters\Filter;

class PostFilter extends Filter
{
    //
}
```
You can use the Filter class like this:
```php
$posts = (new PostFilter(Post::query(), ['title' => 'Post 1', 'sort' => '-title']))
    ->filter()
    ->sort()
    ->get();
```
You can filter and sort by defining separate methods in `PostFilter.php`
```php
// Filter method
protected function title($title)
{
    $this->getQuery()->where('title', $title);
}
// Sort method
protected function sortTitle($order)
{
    $this->getQuery()->orderBy('title', $order);
}
```

Or you can do the basic filters and sorting by adding then in filterable and sortable attributes in `PostFilter.php`
```php
protected array $filterableFields = ['title', 'category', 'slug'];
protected array $sortableFields = ['title', 'category', 'slug'];
```