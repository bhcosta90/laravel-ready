<?php

declare(strict_types = 1);

use Symfony\Component\Process\Process;

if (!function_exists('verifyInstallDependency')) {
    function verifyInstallDependency(string $package): bool
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')));

        return !(array_key_exists($package, (array) $composer->require) || array_key_exists(
            $package,
            (array) $composer->{'require-dev'}
        ));
    }
}

if (!function_exists('executeCommand')) {
    function executeCommand($command): bool
    {
        try {
            Process::fromShellCommandline($command)
                ->setTty(false)
                ->setTimeout(null)
                ->run();

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
