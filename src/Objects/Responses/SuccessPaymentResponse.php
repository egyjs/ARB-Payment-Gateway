<?php

namespace Egyjs\Arb\Objects\Responses;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use stdClass;

/**
 * Class SuccessPayment
 *
 *
 * @method string getAuthRespCode()
 * @method string getAuthCode()
 * @method int getTransId()
 * @method string getTrackId()
 * @method string getCardType()
 * @method string getThreeDSServerTranID()
 * @method string getAcsTranID()
 * @method string getResult()
 * @method string getExpMonth()
 * @method int getRef()
 * @method string getExpYear()
 * @method int getPaymentId()
 * @method int getFcCustId()
 * @method string getDsTranID()
 * @method int getActionCode()
 * @method string getCard()
// * @method object getOriginalData()
 */
class SuccessPaymentResponse extends stdClass
{
    use HasActionCode;

    public bool $success = true;

    public ?object $data = null;

    public string $message = 'Payment Success';

    public function __construct(array $data)
    {
        $this->data = (object) $data;
    }

    public function getDate(): false|Carbon
    {
        // data.date = "0326" // which means 03/26 of March of the current year.
        return Carbon::createFromFormat('md', $this->data->date)
            ->setTimeFrom('00:00:00');
    }

    public function getAmount(): float
    {
        return (float) $this->data->amount;
    }

    public function getCardLast4Digits(): string
    {
        return Str::substr($this->data->card, -4);
    }

    public function getOriginalData(): array|stdClass
    {
        return json_decode(base64_decode($this->data->udf1));
    }

    /**
     * Magic method to handle dynamic method calls to the class.
     *
     * This method is invoked when an inaccessible or non-existing method is called on the class.
     * It allows the class to respond to different method calls dynamically.
     *
     * @param  string  $name  The name of the method being called.
     * @param  array  $arguments  An enumerated array containing the parameters passed to the method.
     * @return mixed The value of the property named by $name.
     */
    public function __call(string $name, array $arguments)
    {
        if (Str::startsWith($name, 'get')) {
            $name = Str::of($name)
                ->after('get')
                ->lcfirst()
                ->replace('_', '')
                ->__toString();

            return $this->data->$name;
        }
    }
}
