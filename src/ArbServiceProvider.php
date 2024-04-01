<?php

namespace Egyjs\Arb;

use Egyjs\Arb\Commands\ArbCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasRoute('web')
            ->hasCommand(ArbCommand::class);
    }
}
