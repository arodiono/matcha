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
    private $headerToken;

    private $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->headerToken = $this->setToken();
    }

    /**
     * @return array
     */
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
        return (['Authorization' => 'Bearer ' . $tok]);
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->headeToken;
    }

    /**
     * @param $url
     * @param array $params
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($url, $params = [])
    {
        return $this->client->get('https://api.intra.42.fr/v2/' . $url, [
            'headers' => $this->headerToken
        ]);
    }

    /**
     * @param $url
     * @param array $params
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function post($url, $params = [])
    {
        return $this->client->post('https://api.intra.42.fr/v2/' . $url, ['form_params' => $params]);
    }

    /**
     * @param $authCode
     * @param $redirectUrl
     * @return mixed
     */
    public function auth($authCode, $redirectUrl)
    {
        $response = $this->post('oauth/token',
            [
                'grant_type' => 'authorization_code',
                'client_id' => INTRA_UID,
                'client_secret' => INTRA_SECRET,
                'code' => $authCode,
                'redirect_uri' => $redirectUrl
            ]);
        $authToken = json_decode($response->getBody()->getContents())->access_token;
        $this->headerToken = ['Authorization' => 'Bearer ' . $authToken];
        return json_decode($this->get('me')->getBody()->getContents());
    }
}