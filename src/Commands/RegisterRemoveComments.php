<?php

declare(strict_types = 1);

namespace Costa\Ready\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class RegisterRemoveComments extends Command
{
    protected $signature = 'ready:remove-comments';

    protected $description = 'Register Remove Comments';

    public function handle(): bool
    {
        $path = text("Which folder do you want to remove comments from?", default: 'app');
        $extension = text("Which extension do you want to remove comments from?", default: 'php');

        return executeCommand(
            'find "'.__DIR__.'/'.$path.'" -type f -name "*.'.$extension.'" -exec sh -c \'sed "/\/\*\*/,/\*\//d" "$0" > "$0.tmp" && mv "$0.tmp" "$0"\' {} \;',
        );
    }
}
