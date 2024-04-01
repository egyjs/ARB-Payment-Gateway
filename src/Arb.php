<?php

/**
 * Class Arb
 *
 * This class handles the payment and refund requests for the ARB Payment Gateway.
 * It also handles the encryption and decryption of data for secure transactions.
 */

namespace Egyjs\Arb;

use Egyjs\Arb\Objects\Card;
use Illuminate\Support\Facades\Http;

class Arb
{
    /**
     * @var string The URL to redirect to after a successful payment.
     */
    protected ?string $success_url;

    /**
     * @var string The URL to redirect to after a failed payment.
     */
    protected ?string $error_url;

    /**
     * @var array The data to be sent with the payment request.
     */
    protected array $data = [];

    /**
     * @var Card|null The card details for the payment.
     */
    protected ?Card $card = null;

    /**
     * Initiates a payment request.
     *
     * @param  int  $amount  The amount to be paid.
     * @return object The response from the payment gateway.
     */
    public function initiatePayment(int $amount): object
    {
        // todo get order id or something
        $trackId = uniqid($amount * time());

        $data = [
            'id' => config('arb.tranportal_id'),
            'password' => config('arb.tranportal_password'),
            'action' => '1',
            'trackId' => $trackId,
            'amt' => (string) $amount,
            'currencyCode' => config('arb.currency_code'),
            'langid' => app()->getLocale(),
            'responseURL' => $this->successUrl(),
            'errorURL' => $this->failUrl(),
            'udf1' => base64_encode(json_encode($this->data())),
        ] + $this->data();

        if ($this->card !== null) {
            $data += $this->card()->toArray();
        }

        $data = $this->createRequestBody($this->wrapData($data));

        return $this->handlePaymentRequest($data);
    }

    /**
     * Handles the result of a payment request.
     *
     * @param  string  $trandata  The encrypted data from the payment gateway.
     * @return object The decrypted and processed payment result.
     */
    public function result(string $trandata): object
    {
        $decrypted = $this->decryption($trandata, config('arb.resource_key'));
        $raw = urldecode($decrypted);
        $dataArr = json_decode($raw, true);
        if (isset($dataArr[0]['errorText'])) {
            return (object) ['success' => false, 'data' => $dataArr[0]];
        }
        $paymentStatus = $dataArr[0]['result'];
        if (isset($paymentStatus) && $paymentStatus === 'CAPTURED') {
            return (object) ['success' => true, 'data' => $dataArr[0]];
        }

        return (object) ['success' => false, 'data' => $dataArr[0]];
    }

    /**
     * Creates the request body for a payment request.
     *
     * @param  string  $encoded_data  The encoded data to be sent with the request.
     * @return string The request body.
     */
    private function createRequestBody($encoded_data): string
    {
        $encryptedData = [
            'id' => config('arb.tranportal_id'),
            'trandata' => $this->encryption($encoded_data, config('arb.resource_key')),
            'responseURL' => $this->successUrl(),
            'errorURL' => $this->failUrl(),
        ];

        return $this->wrapData($encryptedData);
    }

    /**
     * Sends a payment request to the payment gateway and handles the response.
     *
     * @param  string  $data  The request body.
     * @return object The response from the payment gateway.
     */
    public function handlePaymentRequest(string $data): object
    {
        $configName = $this->card !== null
            ? 'merchant_endpoint'
            : 'bank_hosted_endpoint';

        $response = Http::withBody($data, 'application/json')
            ->withOptions(['verify' => false])
            ->post(config('arb.mode') == 'live'
                ? config("arb.live_$configName")
                : config("arb.test_$configName")
            );

        $response = $response->json('0');

        if ($response['status'] == '1') {
            [$paymentID, , $baseURL] = explode(':', $response['result']);
            $baseURL = 'https:'.$baseURL;
            $paymentID = $this->card === null ? '?PaymentID='.$paymentID : '';

            return (object) ['success' => true, 'url' => $baseURL.$paymentID];
        } else {
            return (object) ['success' => false, 'message' => $response['errorText']];
        }
    }

    /**
     * Sends a refund request to the payment gateway and handles the response.
     *
     * @param  string  $data  The request body.
     * @return object The response from the payment gateway.
     */
    public function handleRefundRequest(string $data): object
    {
        $configName = 'merchant_endpoint';

        $response = Http::withBody($data, 'application/json')
            ->withOptions(['verify' => false])
            ->post(config('arb.mode') == 'live'
                ? config("arb.live_$configName")
                : config("arb.test_$configName")
            );

        $response = $response->json('0');

        if ($response['status'] == '1') {
            return $this->result($response['trandata']);
        } else {
            return (object) ['success' => false, 'message' => $response['errorText']];
        }
    }

    /**
     * Initiates a refund request.
     *
     * @param  string  $transId  The transaction ID of the payment to be refunded.
     * @param  string  $trackId  The tracking ID of the payment to be refunded.
     * @return object The response from the payment gateway.
     */
    public function refund(string $transId, string $trackId): object
    {
        $data = [
            'id' => config('arb.tranportal_id'),
            'password' => config('arb.tranportal_password'),
            'action' => '2',
            'responseURL' => $this->successUrl(),
            'errorURL' => $this->failUrl(),
            'trackId' => $trackId,
            'transId' => $transId,
            'currencyCode' => config('arb.currency_code'),
            'amt' => '1001',
            'langid' => app()->getLocale(),
        ] + $this->data();

        $data = $this->createRequestBody($this->wrapData($data));

        return $this->handleRefundRequest($data);
    }

    /**
     * Encrypts the given string with the given key.
     *
     * @param  string  $str  The string to be encrypted.
     * @param  string  $key  The encryption key.
     * @return string The encrypted string.
     */
    private function encryption(string $str, string $key): string
    {
        $blocksize = openssl_cipher_iv_length('AES-256-CBC');
        $pad = $blocksize - (strlen($str) % $blocksize);
        $str = $str.str_repeat(chr($pad), $pad);
        $encrypted = openssl_encrypt($str, 'AES-256-CBC', $key, OPENSSL_ZERO_PADDING, 'PGKEYENCDECIVSPC');
        $encrypted = base64_decode($encrypted);
        $encrypted = unpack('C*', ($encrypted));
        $chars = array_map('chr', $encrypted);
        $bin = implode($chars);
        $encrypted = bin2hex($bin);

        return urlencode($encrypted);
    }

    /**
     * Decrypts the given string with the given key.
     *
     * @param  string  $code  The string to be decrypted.
     * @param  string  $key  The decryption key.
     * @return false|string The decrypted string or false if the decryption failed.
     */
    private function decryption($code, $key): false|string
    {
        $string = hex2bin(trim($code));
        $code = unpack('C*', $string);
        $chars = array_map('chr', $code);
        $code = implode($chars);
        $code = base64_encode($code);
        $decrypted = openssl_decrypt($code, 'AES-256-CBC', $key, OPENSSL_ZERO_PADDING, 'PGKEYENCDECIVSPC');
        $pad = ord($decrypted[strlen($decrypted) - 1]);
        if ($pad > strlen($decrypted)) {
            return false;
        }
        if (strspn($decrypted, chr($pad), strlen($decrypted) - $pad) != $pad) {
            return false;
        }

        return urldecode(substr($decrypted, 0, -1 * $pad));
    }

    /**
     * Wraps the given data array into a JSON string and encloses it in square brackets.
     *
     * @param  array  $data  The data to be wrapped.
     * @return string The wrapped data as a JSON string enclosed in square brackets.
     */
    private function wrapData(array $data): string
    {
        $data = json_encode($data);

        return "[$data]";
    }

    /**
     * Sets or gets the data to be sent with the payment request.
     *
     * @param  array|null  $data  The data to be set. If null, the current data is returned.
     * @return array|self The current data or the Arb object for method chaining.
     */
    public function data(?array $data = null): array|self
    {
        if ($data) {
            $this->data = $data;

            return $this;
        }

        return $this->data;
    }

    /**
     * Sets or gets the success URL.
     *
     * @param  string|null  $url  The URL to be set. If null, the current URL is returned.
     * @return string|self The current URL or the Arb object for method chaining.
     */
    public function successUrl(?string $url = null): string|self
    {
        if ($url) {
            $this->success_url = $url;

            return $this;
        }

        return $this->success_url ?? config('arb.redirect.success');
    }

    /**
     * Sets or gets the error URL.
     *
     * @param  string|null  $url  The URL to be set. If null, the current URL is returned.
     * @return string|self The current URL or the Arb object for method chaining.
     */
    public function failUrl(?string $url = null): string|self
    {
        if ($url) {
            $this->error_url = $url;

            return $this;
        }

        return $this->error_url ?? config('arb.redirect.fail');
    }

    /**
     * Sets or gets the card details.
     *
     * @param  array|null  $card  The card details to be set. If null, the current card details are returned.
     * @return Card|self The current card details or the Arb object for method chaining.
     */
    public function card(?array $card = null): Card|self
    {
        if ($card) {
            $this->card = new Card($card);

            return $this;
        }

        return $this->card;
    }
}
