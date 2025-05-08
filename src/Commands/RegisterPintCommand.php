<?php

declare(strict_types = 1);

namespace Costa\Ready\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

use function Laravel\Prompts\text;

final class RegisterPintCommand extends Command
{
    protected $signature = 'ready:register-pint';

    protected $description = 'Install Pint for your application';

    public function handle(): void
    {
        $this->info('Installing Pint...');

        if (verifyInstallDependency('laravel/pint')) {
            executeCommand('composer require laravel/pint --dev');
        }

        $endpoint = text(
            'What address is your pint configuration?',
            default: 'https://raw.githubusercontent.com/bhcosta90/laravel-ready/refs/heads/main/fixtures/pint.json'
        );

        $content = (new Client())->get($endpoint);

        file_put_contents(
            base_path('pint.json'),
            $content->getBody()->getContents()
        );

        $this->info('Pint already installed');
    }
}
