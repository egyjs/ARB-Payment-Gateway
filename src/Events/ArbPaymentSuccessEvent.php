<?php

namespace Egyjs\Arb\Events;

use Egyjs\Arb\Objects\Responses\SuccessPaymentResponse;
use Illuminate\Foundation\Events\Dispatchable;

class ArbPaymentSuccessEvent
{
    use Dispatchable;

    public function __construct(public SuccessPaymentResponse $response)
    {
    }
}
