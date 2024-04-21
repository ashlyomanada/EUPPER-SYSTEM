<?php

namespace App\Helpers;

use GuzzleHttp\Client;

class InfobipHelper
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = 'fdc0fc4b3cf35557937435f41a072b43-ab235ced-7004-4b69-b1b1-166957845f9b';
        $this->baseUrl = 'w1eley.api.infobip.com';
    }

    public function sendSms($to, $message)
    {
        $client = new Client();

        try {
            $response = $client->post($this->baseUrl, [
                'headers' => [
                    'Authorization' => 'App ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'from' => 'Euper',
                    'to' => $to,
                    'text' => $message,
                ],
            ]);

            return $response->getStatusCode() == 200;
        } catch (\Exception $e) {
            // Handle exceptions (e.g., log errors)
            return false;
        }
    }
}