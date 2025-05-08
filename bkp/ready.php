<?php

declare(strict_types = 1);

use GuzzleHttp\Client;

use function Laravel\Prompts\{error, info, multiselect, select, text};

use Symfony\Component\Process\Process;

$env = environment();

if (!file_exists($autoload = __DIR__ . '/vendor/autoload.php')) {
exit("Please, run \"composer install\" before running this script." . PHP_EOL);
}

if ($env['APP_ENV'] === 'production') {
exit("For safety, you cannot run this script in production." . PHP_EOL);
}

require $autoload;

$actions = [
'executePintPreparation'            => '[Dev. Tool] Install Laravel Pint',
'executeLaraStanPreparation'        => '[Dev. Tool] Install LaraStan',
'executeLaravelDebugBarPreparation' => '[Dev. Tool] Install Laravel DebugBar',
'executeIdeHelperPreparation'       => '[Dev. Tool] Install Laravel IDE Helper',
'executeClockWorkPreparation'       => '[Dev. Tool] Install Clock Work',
'executePhpRectorPreparation'       => '[Dev. Tool] Install Php Rector',
'executeLivewirePreparation'        => 'Install Livewire',
'executeCommentsRemoval'            => 'Remove Unnecessary Laravel Comments',
'executeCaptainHook'                => 'Creating captain hook',
];

$type = select('What do you want to do?', [
'packages' => 'Install Dev. Tools Packages',
'project'  => 'Prepare New Project',
]);

if ($type === 'packages') {
$packages = collect($actions)
    ->filter(fn ($key) => str_contains($key, '[Dev. Tool]'))
    ->mapWithKeys(fn ($value, $key) => [$key => str_replace('[Dev. Tool] ', '', $value)])
    ->toArray();

$steps = multiselect('Select the packages:', $packages, scroll: 20, required: true);
} else {
$actions = collect($actions)
    ->map(fn ($value) => str_replace('[Dev. Tool] ', '', $value))
    ->toArray();

$steps = multiselect('What do you want to do?', $actions, scroll: 20, required: true);
}

/** Steps Execution */
foreach ($steps as $step) {
info($step . "...");

try {
    if (($result = $step()) !== true) {
        throw new Exception($result);
    }

    info($step . " ✅") . PHP_EOL;
} catch (Exception $exception) {
    error($exception->getMessage()) . PHP_EOL;
    result($exception->getMessage());
}
}
/** End Steps Execution */
function environment(): array
{
$response = [];
$handle   = fopen(".env", "r");

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $arrayLine = explode("=", $line);
        $first     = array_shift($arrayLine);

        if (!empty($first)) {
            $response[$first] = implode("=", $arrayLine);
        }
    }

    fclose($handle);
}

return $response;
}

function executePintPreparation(): bool | string
{
try {
    if (verifyInstallDependency("laravel/pint") && ($status = executeCommand(
            "composer require laravel/pint --dev"
        )) !== true) {
        return $status;
    }

    $endpoint = text(
        'What address is your pint configuration?',
        default: 'https://raw.githubusercontent.com/bhcosta90/laravel-ready/main/pint.json'
    );

    $content = (new Client())->get($endpoint);

    file_put_contents(
        'pint.json',
        $content->getBody()->getContents()
    );

    $composer = json_decode(file_get_contents('composer.json'));

    $composer->scripts->{'pint:format'} = './vendor/bin/pint';
    $composer->scripts->{'pint:test'}   = './vendor/bin/pint --test';
    file_put_contents('composer.json', json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    executeCommand('composer run pint:format');

    return true;
} catch (Exception $e) {
    return $e->getMessage();
}
}

function executeLaraStanPreparation(): bool | string
{
try {
    if (verifyInstallDependency("larastan/larastan") && ($status = executeCommand(
            "composer require larastan/larastan --dev"
        )) !== true) {
        return $status;
    }

    $endpoint = text(
        'What address is your lara stan configuration?',
        default: 'https://raw.githubusercontent.com/bhcosta90/laravel-ready/main/phpstan.neon'
    );

    $content = (new Client())->get($endpoint);

    file_put_contents(
        'phpstan.neon',
        $content->getBody()->getContents()
    );

    $composer                   = json_decode(file_get_contents('composer.json'));
    $composer->scripts->analyse = './vendor/bin/phpstan analyse';
    file_put_contents('composer.json', json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    return true;
} catch (Exception $e) {
    return $e->getMessage();
}
}

function executeLaravelDebugBarPreparation(): bool | string
{
if (verifyInstallDependency("barryvdh/laravel-debugbar")) {
    return executeCommand("composer require barryvdh/laravel-debugbar --dev");
}

return 'Laravel debugbar is already installed';
}

function executeIdeHelperPreparation(): bool | string
{
if (verifyInstallDependency("barryvdh/laravel-ide-helper")) {
    return executeCommand("composer require barryvdh/laravel-ide-helper --dev");
}

return 'Laravel ide helper is already installed';
}

function executeLivewirePreparation(): bool | string
{
if (!verifyInstallDependency('livewire/livewire')) {
    return 'Livewire is already installed in a different version.';
}

$livewire = select('Select Livewire version:', [
    'livewire/livewire:^3.0' => 'Livewire [3.x]',
    'livewire/livewire:^2.0' => 'Livewire [2.x]',
]);

try {
    if (($result = executeCommand("composer require $livewire")) !== true) {
        return $result;
    }

    if ($livewire === 'livewire/livewire:^2.0') {
        return true;
    }

    return executeAlpineJsRemovalPreparation();
} catch (Exception $e) {
    return $e->getMessage();
}
}

function executeAlpineJsRemovalPreparation(): bool | string
{
try {
    if (($status = executeCommand("npm remove alpinejs")) !== true) {
        return $status;
    }

    unlink(__DIR__ . '/resources/js/app.js');
    file_put_contents(__DIR__ . '/resources/js/app.js', "import './bootstrap';");

    return executeCommand("npm run build");
} catch (Exception $e) {
    return $e->getMessage();
}
}

function executeCommentsRemoval(): bool
{
$path      = text("Which folder do you want to remove comments from?", default: 'app');
$extension = text("Which extension do you want to remove comments from?", default: 'php');

return executeCommand(
    'find "' . __DIR__ . '/' . $path . '" -type f -name "*.' . $extension . '" -exec sh -c \'sed "/\/\*\*/,/\*\//d" "$0" > "$0.tmp" && mv "$0.tmp" "$0"\' {} \;'
);
}

function executeCaptainHook()
{
if (!is_dir('.git')) {
    executeCommand('git init');
}

$executeWhen = select('What type of execution do you wanna do??', [
    'pre-push',
    'pre-commit',
], scroll: 20, required: true);

$executionSteps = multiselect('What do you want to do?', [
    'test',
    'lara stan',
    'pint',
], scroll: 20, required: true);

$actions = [];

foreach ($executionSteps as $step) {
    switch ($step) {
        case 'lara stan':
            $actions[] = "./vendor/bin/phpstan analyse";

            break;
        case 'test':
            $actions[] = !verifyInstallDependency('pestphp/pest')
                ? "./vendor/bin/pest --parallel"
                : "./vendor/bin/phpunit";

            break;
        case 'pint':
            $actions[] = "./vendor/bin/pint --test";

            break;
    }
}

$content = "";

foreach ($actions as $key => $action) {
    if ($key > 0) {
        $content .= "\n";
    }
    $content .= "           {\"action\": \"{$action}\"},";
}

$content = substr($content, 0, -1);

if (verifyInstallDependency('captainhook/captainhook') && executeCommand(
        "composer require captainhook/captainhook --dev"
    ) !== true) {
    return false;
}

if (verifyInstallDependency('captainhook/hook-installer') && executeCommand(
        "composer require captainhook/hook-installer --dev"
    ) !== true) {
    return false;
}

$content = <<<CONTENT
{
"{$executeWhen}": {
    "enabled": true,
    "actions": [
{$content}
    ]
}
}
CONTENT;

file_put_contents('captainhook.json', $content);

if (executeCommand("./vendor/bin/captainhook install -f -s") !== true) {
    return false;
}

return true;
}

function executeClockWorkPreparation()
{
if (verifyInstallDependency("itsgoingd/clockwork")) {
    return executeCommand("composer require itsgoingd/clockwork --dev");
}

return 'Clock work is already installed';
}

function executePhpRectorPreparation()
{
if (verifyInstallDependency("rector/rector")) {
    return executeCommand("composer require rector/rector --dev");
}

$endpoint = text(
    'What address is your php rector configuration?',
    default: 'https://raw.githubusercontent.com/bhcosta90/laravel-ready/main/php-rector.php'
);

$content = (new Client())->get($endpoint);

file_put_contents(
    'rector.php',
    $content->getBody()->getContents()
);

$composer                           = json_decode(file_get_contents('composer.json'));
$composer->scripts->{'rector:exec'} = './vendor/bin/rector process';
$composer->scripts->{'rector:dry'}  = './vendor/bin/rector process --dry-run';
file_put_contents('composer.json', json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

return true;
}

function result(string $message): void
{
file_put_contents('storage/logs/laravel-ready.log', $message . PHP_EOL, FILE_APPEND);
}

function executeCommand(string $command): bool | string
{
try {
    Process::fromShellCommandline("$command")
        ->setTty(false)
        ->setTimeout(null)
        ->run();

    return true;
} catch (Exception $e) {
    return $e->getMessage();
}
}

function verifyInstallDependency(string $package): bool
{
$composer = json_decode(file_get_contents('composer.json'));

return !(array_key_exists($package, (array) $composer->require) || array_key_exists(
        $package,
        (array) $composer->{'require-dev'}
    ));
}

if ($env['APP_ENV'] !== 'github') {
$type = select('Do you want to remove this file?', [
    true  => 'True',
    false => 'False',
]);

if ($type) {
    info("Your project is ready to be used! 🚀 Deleting script in 5 seconds...");
    sleep(5);
    unlink(__FILE__);
}
}
