<?php

namespace App\Services;

class APIHeaderGenerator {
    public function generateApiHeader(): array
    {
        date_default_timezone_set('UTC');
        $time_stamp = strtotime('now');

        $consumer_id = "123456";
        $secret_key = '0034T2';

        $signature = hash_hmac('sha256', $time_stamp.$consumer_id, $secret_key, true);
        $signature_encode = base64_encode($signature);

        return [
            'Accept' => 'application/json',
            'X-cons-id' => $consumer_id,
            'X-signature' => $signature_encode,
            'X-timestamp' => $time_stamp,
        ];
    }
}
