<?php

namespace Zihad\Pilter\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;


class MakeFilterCommand extends GeneratorCommand
{
    protected $signature = "make:filter {name : The Model Name}";

    protected $description = "Create a new filter";

    protected $type = 'Filter';

    protected function getStub(): string
    {
        $file = 'Filter.stub';
        return __DIR__ . "/../../stubs/{$file}";
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return "{$rootNamespace}\\Filters";
    }

    
    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'Filter.php';
    }

}
