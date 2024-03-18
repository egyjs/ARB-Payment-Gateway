<?php

namespace Egyjs\Arb\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Egyjs\Arb\Arb
 */
class Arb extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Egyjs\Arb\Arb::class;
    }
}
