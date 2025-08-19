<?php

namespace App\Services;

class APIBpjsHeaderGenerator {
    public function generateApiBpjsHeader(): array
    {
        date_default_timezone_set('UTC');
        $time_stamp = strtotime('now');

        $consumer_id = "25796";
        $consumer_secret = "4qP1E30D6D";
        $user_key = 'fbb8c54a30614c210d5b1a6b1c50944d';

        $signature = hash_hmac('sha256', $consumer_id . '&' . $time_stamp, $consumer_secret, true);
        $signature_encode = base64_encode($signature);

        return [
            'Accept' => 'application/json',
            'X-cons-id' => $consumer_id,
            'X-signature' => $signature_encode,
            'X-timestamp' => $time_stamp,
            'userkey' => $user_key
        ];
    }
}
