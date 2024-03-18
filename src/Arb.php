<?php

namespace Egyjs\Arb;

class Arb
{
    public function merchantPayment(array $paymentData, int $amount = 0, string $response_url, string $error_url, array $optionalData = [])
    {
        $trackId = rand(111111111, 999999999);

        $data = [
                "id" => config('ArjBank.tranportal_id'),
                "password" => config('ArjBank.tranportal_password'),
                "action" => "1",
                "currencyCode" => config('ArjBank.currency_code'),
                "responseURL" => $response_url,
                "errorURL" => $error_url,
                "trackId" => (string) $trackId,
                "amt" => (string) $amount,
            ] + $paymentData + $optionalData;

        $encoded_data = $this->wrapData(json_encode($data));

        $encryptedData = [
            "id" => config('ArjBank.tranportal_id'),
            "trandata" => $this->encryption($encoded_data, config('ArjBank.resource_key')),
            "responseURL" => $response_url,
            "errorURL" => $error_url,
        ];

        $encodedData = $this->wrapData(json_encode($encryptedData));

        $response = Http::withBody($encodedData, 'application/json')->withOptions(['verify' => false])
            ->post(config('ArjBank.mode') == 'live' ? config('ArjBank.live_merchant_endpoint') : config('ArjBank.test_merchant_endpoint'));

        $response_data = json_decode($response, true)[0];

        if ($response_data["status"] == "1") {
            $url = "https:" . explode(":", $response_data["result"])[2];
            return ["status" => '1', "url" => $url];
        } else {
            return ["status" => '2', "message" => $response_data["errorText"]];
        }
    }

    public function bankHostedPayment(int $amount = 0, string $response_url, string $error_url, array $optionalData = [])
    {
        $trackId = rand(111111111, 999999999);

        $data = [
                "id" => config('ArjBank.tranportal_id'),
                "password" => config('ArjBank.tranportal_password'),
                "action" => "1",
                "currencyCode" => "682",
                "responseURL" => $response_url,
                "errorURL" => $error_url,
                "trackId" => (string) $trackId,
                "amt" => (string) $amount,
            ] + $optionalData;

        $encoded_data = $this->wrapData(json_encode($data));

        $encryptedData = [
            "id" => config('ArjBank.tranportal_id'),
            "trandata" => $this->encryption($encoded_data, config('ArjBank.resource_key')),
            "responseURL" => $response_url,
            "errorURL" => $error_url,
        ];

        $encodedData = $this->wrapData(json_encode($encryptedData));

        $response = Http::withBody($encodedData, 'application/json')->withOptions(['verify' => false])
            ->post(config('ArjBank.mode') == 'live' ? config('ArjBank.live_bank_hosted_endpoint') : config('ArjBank.test_bank_hosted_endpoint'));

        $response_data = json_decode($response, true)[0];

        if ($response_data["status"] == "1") {
            $url = "https:" . explode(":", $response_data["result"])[2] . '?PaymentID=' . explode(":", $response_data["result"])[0];
            return ["status" => '1', "url" => $url];
        } else {
            return ["status" => '2', "message" => $response_data["errorText"]];
        }
    }

    public function result($data)
    {
        $decrypted = $this->decryption($data, config('ArjBank.resource_key'));
        $raw = urldecode($decrypted);
        $dataArr = json_decode($raw, true);
        if (isset($dataArr[0]['errorText'])) {
            return ["status" => '2', 'data' => $dataArr[0]];
        }
        $paymentStatus = $dataArr[0]["result"];
        if (isset($paymentStatus) && $paymentStatus === 'CAPTURED') {
            return ["status" => '1', 'data' => $dataArr[0]];
        }
        return ["status" => '2', 'data' => $dataArr[0]];
    }

    private function encryption($str, $key)
    {
        $blocksize = openssl_cipher_iv_length("AES-256-CBC");
        $pad = $blocksize - (strlen($str) % $blocksize);
        $str = $str . str_repeat(chr($pad), $pad);
        $encrypted = openssl_encrypt($str, "AES-256-CBC", $key, OPENSSL_ZERO_PADDING, "PGKEYENCDECIVSPC");
        $encrypted = base64_decode($encrypted);
        $encrypted = unpack('C*', ($encrypted));
        $chars = array_map("chr", $encrypted);
        $bin = join($chars);
        $encrypted = bin2hex($bin);
        $encrypted = urlencode($encrypted);
        return $encrypted;
    }

    private function decryption($code, $key)
    {
        $string = hex2bin(trim($code));
        $code = unpack('C*', $string);
        $chars = array_map("chr", $code);
        $code = join($chars);
        $code = base64_encode($code);
        $decrypted = openssl_decrypt($code, "AES-256-CBC", $key, OPENSSL_ZERO_PADDING, "PGKEYENCDECIVSPC");
        $pad = ord($decrypted[strlen($decrypted) - 1]);
        if ($pad > strlen($decrypted)) {
            return false;
        }
        if (strspn($decrypted, chr($pad), strlen($decrypted) - $pad) != $pad) {
            return false;
        }
        return urldecode(substr($decrypted, 0, -1 * $pad));
    }

    private function wrapData($data)
    {
        $data = <<<EOT
[$data]
EOT;
        return $data;
    }
}
