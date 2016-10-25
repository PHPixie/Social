<?php

namespace PHPixie\Social\Providers\Dropbox;

class Provider extends \PHPixie\Social\OAuth\OAuth2\Provider
{
    protected $loginDataEndpoint = 'users/get_current_account';

    protected function endpointUrl($endpoint)
    {
        $version = $this->configData->get('apiVersion', '2');

        return 'https://api.dropboxapi.com/' . $version . '/' . $endpoint;
    }

    public function loginUrl($callbackUrl, $additionalScope = array())
    {
        return $this->buildLoginUrl(
            'https://www.dropbox.com/oauth2/authorize',
            $callbackUrl,
            array(
                'response_type' => 'code'
            )
        );
    }

    public function handleCallback($callbackUrl, $callbackData)
    {
        if (!isset($callbackData['code']))
        {
            return null;
        }

        $baseParameters = array(
            'client_id'     => $this->configData->getRequired('appId'),
            'client_secret' => $this->configData->getRequired('appSecret'),
            'redirect_uri'  => $callbackUrl,
            'code'          => $callbackData['code'],
            'grant_type'    => 'authorization_code'
        );

        $tokenData = $this->getTokenResponse($callbackData, $baseParameters);
        $tokenData = $this->decodeApiResponse($tokenData);

        $headers = array(
            'Authorization' => 'Bearer ' . $tokenData->access_token,
            'Content-Type'  => 'application/json; charset=utf-8'
        );

        $loginData = $this->apiCall(
            $tokenData->access_token,
            'POST',
            $this->loginDataEndpoint,
            array(),
            "null",
            $headers
        );

        $loginData = $this->normalizeLoginData($loginData, $tokenData);

        $token = $this->buildToken($tokenData, $loginData);

        return $this->user($token, $loginData);
    }

    protected function apiCall($accessToken, $method, $endpoint, $query = array(), $data = null, $headers = array())
    {
        $url = $this->endpointUrl($endpoint);

        $response = $this->http()->call($method, $url, $query, $data, $headers);

        return $this->decodeApiResponse($response);
    }

    protected function getTokenResponse($callbackData, $baseParameters)
    {
        return $this->http()->call(
            'POST',
            'https://api.dropboxapi.com/oauth2/token',
            $baseParameters
        );
    }

    public function type()
    {
        return 'dropbox';
    }

    protected function buildToken($tokenData, $loginData)
    {
        $expiresIn    = $this->configData->get('expiresIn', 2592000);

        return $this->token(
            $loginData->account_id,
            $tokenData->access_token,
            $expiresIn
        );
    }
}
