<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 5/9/18
 * Time: 8:31 PM
 */

namespace App\Services;


use GuzzleHttp\Client;

class IntraHelper
{
    private $token;

    private $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->headerToken = $this->setToken();
    }

    private function setToken()
    {
        $token_request = "https://api.intra.42.fr/oauth/token";
        $token = curl_init();
        curl_setopt_array($token, array(
            CURLOPT_URL => $token_request,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => array(
                "grant_type" => "client_credentials",
                "client_id" => INTRA_UID,
                "client_secret" => INTRA_SECRET
            )
        ));
        curl_setopt($token, CURLOPT_RETURNTRANSFER, true);
        $tok = json_decode(curl_exec($token))->access_token;
        curl_close($token);
        return ('Authorization: Bearer ' . $tok);
    }

    public function getToken()
    {
        return $this->token;
    }
}