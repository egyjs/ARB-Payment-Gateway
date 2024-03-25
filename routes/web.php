<?php

use Egyjs\Arb\Events\ArbPaymentFailedEvent;
use Egyjs\Arb\Events\ArbPaymentSuccessEvent;
use Egyjs\Arb\Facades\Arb;

Route::post('/arb/response', function () {
    if(request()->has('trandata')) {
        $data = request()->trandata;
        $result = Arb::result($data);
        if($result->success) {
            // payment success
            event(new ArbPaymentSuccessEvent($result->data));
        } else {
            // payment failed
            event(new ArbPaymentFailedEvent($result->data));
        }
        dd($result);
    }
});
