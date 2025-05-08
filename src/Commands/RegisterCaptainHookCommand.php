<?php

declare(strict_types = 1);

namespace Costa\Ready\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class RegisterCaptainHookCommand extends Command
{
    protected $signature = 'ready:register-captain-hook';

    protected $description = 'Install CaptainHook for your application';

    public function handle(): void
    {
        $this->info('Installing CaptainHook...');

        if (verifyInstallDependency('captainhook/captainhook')) {
            executeCommand('composer require captainhook/captainhook --dev');
        }

        if (verifyInstallDependency('captainhook/hook-installer')) {
            executeCommand('composer require captainhook/hook-installer --dev');
        }

        $options = [
            './vendor/bin/pint --test' => 'pint',
            './vendor/bin/rector process' => 'rector',
            './vendor/bin/phpstan analyse --memory-limit=2G' => 'stan',
        ];

        if(!file_exists(base_path('captainhook.json'))) {
            file_put_contents(
                base_path('captainhook.json'),
                json_encode([
                    'pre-commit' => []
                ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            );
        }

        $composer = json_decode(file_get_contents(base_path('captainhook.json')));

        $preCommit = multiselect(
            'In Pre Commit, what actions do you want to perform at Captain Hook',
            options: $options
        );
        $composer->{'pre-commit'} = [
            'enabled' => true,
            'actions' => $this->convertOptions($preCommit)
        ];

        $preCommit = multiselect(
            'In Pre Push, what actions do you want to perform at Captain Hook',
            options: $options
        );
        $composer->{'pre-push'} = [
            'enabled' => true,
            'actions' => $this->convertOptions($preCommit)
        ];

        file_put_contents(base_path('captainhook.json'), json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        putenv('HOME=' . base_path('.git'));
        executeCommand('vendor/bin/captainhook install --force');
        $this->info('CaptainHook already installed');
    }

    protected function convertOptions(array $options) {
        return array_map(fn($option) => [
            'action' => $option,
        ], $options);
    }
}
