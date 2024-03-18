<?php

namespace Egyjs\Arb\Commands;

use Illuminate\Console\Command;

class ArbCommand extends Command
{
    public $signature = 'arb';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
