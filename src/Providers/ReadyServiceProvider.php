<?php

declare(strict_types = 1);

namespace Costa\Ready\Providers;

use Costa\Ready\Commands;
use Illuminate\Support\ServiceProvider;

final class ReadyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            Commands\RegisterDebugBarCommand::class,
            Commands\RegisterCaptainHookCommand::class,
            Commands\RegisterPintCommand::class,
            Commands\RegisterStanCommand::class,
            Commands\RegisterRectorCommand::class,
        ]);
    }

    public function boot()
    {
    }
}
