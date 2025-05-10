<?php

declare(strict_types = 1);

namespace Costa\Ready\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class RegisterQuit extends Command
{
    protected $signature = 'ready:remove-comments';

    protected $description = 'Register Remove Comments';

    public function handle(): bool
    {
        return executeCommand(
            'composer remove bhcosta90/laravel-ready',
        );
    }
}
