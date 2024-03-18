<?php

namespace Egyjs\Arb;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Egyjs\Arb\Commands\ArbCommand;

class ArbServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('arb')
            ->hasConfigFile()
            ->hasCommand(ArbCommand::class);
    }
}
