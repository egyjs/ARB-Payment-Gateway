<?php

namespace Egyjs\Arb\Objects\Responses;

trait HasActionCode
{
    const PAYMENT_ACTION = 1;
    const REFUND_ACTION = 2;

    public const actions = [
        self::PAYMENT_ACTION => 'PAYMENT',
        self::REFUND_ACTION => 'REFUND',
    ];

    public function getAction() : string
    {
        return self::actions[(int) $this->data->actionCode];
    }
}
