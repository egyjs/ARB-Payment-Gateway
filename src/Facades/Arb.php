<?php

namespace Egyjs\Arb\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Egyjs\Arb\Arb
 *
 * @see \Egyjs\Arb\Arb
 */
class Arb extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Egyjs\Arb\Arb::class;
    }
}
