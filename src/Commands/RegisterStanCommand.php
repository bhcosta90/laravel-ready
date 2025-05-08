<?php

declare(strict_types = 1);

namespace Costa\Ready\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

use function Laravel\Prompts\text;

final class RegisterStanCommand extends Command
{
    protected $signature = 'ready:register-stan';

    protected $description = 'Install Stan for your application';

    public function handle(): void
    {
        $this->info('Installing Stan...');

        if (verifyInstallDependency('larastan/larastan')) {
            executeCommand('composer require larastan/larastan --dev');
        }

        $endpoint = text(
            'What address is your stan configuration?',
            default: 'https://raw.githubusercontent.com/bhcosta90/laravel-ready/main/fixtures/phpstan.neon'
        );

        $content = (new Client())->get($endpoint);

        file_put_contents(
            base_path('phpstan.neon'),
            $content->getBody()->getContents()
        );

        $this->info('Stan already installed');
    }
}
