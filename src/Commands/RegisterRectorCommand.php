<?php

declare(strict_types = 1);

namespace Costa\Ready\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

use function Laravel\Prompts\text;

final class RegisterRectorCommand extends Command
{
    protected $signature = 'ready:register-rector';

    protected $description = 'Install Rector for your application';

    public function handle(): void
    {
        $this->info('Installing Rector...');

        if (verifyInstallDependency('rector/rector')) {
            executeCommand('composer require rector/rector --dev');
        }

        $endpoint = text(
            'What address is your php rector configuration?',
            default: 'https://raw.githubusercontent.com/bhcosta90/laravel-ready/main/fixtures/rector.php'
        );

        $content = (new Client())->get($endpoint);

        file_put_contents(
            base_path('rector.php'),
            $content->getBody()->getContents()
        );

        $this->info('Rector already installed');
    }
}
