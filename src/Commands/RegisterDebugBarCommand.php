<?php

declare(strict_types = 1);

namespace Costa\Ready\Commands;

use Illuminate\Console\Command;

final class RegisterDebugBarCommand extends Command
{
    protected $signature = 'ready:register-debug-bar';

    protected $description = 'Install DebugBar for your application';

    public function handle(): void
    {
        $this->info('Installing DebugBar...');

        if (verifyInstallDependency('barryvdh/laravel-debugbar')) {
            executeCommand('composer require barryvdh/laravel-debugbar --dev');
        }

        $this->info('DebugBar already installed');
    }
}
