<?php

namespace Zihad\Pilter;

use Illuminate\Support\ServiceProvider;
use Zihad\Pilter\Commands\MakeFilterCommand;


class PilterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands(
                commands: [
                    MakeFilterCommand::class,
                ],
            );
        }
    }
}
