<?php

namespace PHPixie\Social\OAuth\OAuth2;

abstract class Provider extends \PHPixie\Social\OAuth\Provider
{
    protected $loginDataEndpoint;

    protected function buildLoginUrl($url, $callbackUrl, $data = array())
    {
        $query = array_merge($data, array(
            'client_id'    => $this->configData->getRequired('appId'),
            'redirect_uri' => $callbackUrl
        ));

        return $this->http()->buildUrl($url, $query);
    }

    public function handleCallback($callbackUrl, $callbackData)
    {
        if(!isset($callbackData['code'])) {
            return null;
        }

        $baseParameters = array(
            'client_id'     => $this->configData->getRequired('appId'),
            'client_secret' => $this->configData->getRequired('appSecret'),
            'redirect_uri'  => $callbackUrl,
            'code'          => $callbackData['code']
        );

        $tokenData = $this->getTokenResponse($callbackData, $baseParameters);
        $tokenData = $this->decodeApiResponse($tokenData);

        $loginData = $this->apiCall(
            $tokenData->access_token,
            'GET',
            $this->loginDataEndpoint
        );

        $loginData = $this->normalizeLoginData($loginData, $tokenData);

        $token = $this->token(
            $this->getUserId($loginData),
            $tokenData->access_token,
            $tokenData->expires_in
        );

        return $this->user($token, $loginData);
    }

    protected function normalizeLoginData($loginData, $tokenData)
    {
        return $loginData;
    }

    public function api($token, $method, $endpoint, $query = array(), $data = null)
    {
        $accessToken = $token->accessToken();
        return $this->apiCall($accessToken, $method, $endpoint, $query, $data);
    }

    protected function apiCall($accessToken, $method, $endpoint, $query = array(), $data = null)
    {
        $url = $this->endpointUrl($endpoint);
        $query['access_token'] = $accessToken;

        $response = $this->http()->call($method, $url, $query, $data);
        return $this->decodeApiResponse($response);
    }

    protected function decodeApiResponse($response)
    {
        return $this->format()->jsonDecode($response);
    }

    protected function token($userId, $accessToken, $expiresIn)
    {
        return new Token(
            $this->name,
            $userId,
            $accessToken,
            $expiresIn
        );
    }

    protected function getUserId($loginData)
    {
        return $loginData->id;
    }

    abstract protected function endpointUrl($url);
    abstract protected function getTokenResponse($callbackData, $baseParameters);
}
