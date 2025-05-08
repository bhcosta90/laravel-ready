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
            default: 'https://raw.githubusercontent.com/bhcosta90/laravel-ready/refs/heads/main/fixtures/phpstan.neon'
        );

        $content = (new Client())->get($endpoint);

        file_put_contents(
            base_path('phpstan.neon'),
            $content->getBody()->getContents()
        );

        $composer = json_decode(file_get_contents(base_path('composer.json')));
        $composer->scripts->analyse = 'phpstan analyse --memory-limit=2G';
        file_put_contents(base_path('composer.json'), json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $this->info('Stan already installed');
    }
}
