<?php

namespace PHPixie\Social\Providers\Instagram;

class Provider extends \PHPixie\Social\OAuth\OAuth2\Provider
{
    protected $loginDataEndpoint = 'users/self';

    protected function endpointUrl($endpoint)
    {
        $version = $this->configData->get('apiVersion', '1');

        return 'https://api.instagram.com/v' . $version . '/' . $endpoint;
    }

    public function loginUrl($callbackUrl, $additionalScope = array())
    {
        $scope = array_merge(
            $this->configData->get('scope', array()),
            $additionalScope
        );

        return $this->buildLoginUrl(
            'https://api.instagram.com/oauth/authorize/',
            $callbackUrl,
            array(
                'scope'         => implode(' ', $scope),
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
            'grant_type'    => 'authorization_code',
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

        $expiresIn    = $this->configData->get('expiresIn', 2592000);

        $token = $this->token(
            $this->getUserId($loginData),
            $tokenData->access_token,
            $expiresIn
        );

        return $this->user($token, $loginData);
    }

    protected function getUserId($loginData)
    {
        return $loginData->data->id;
    }

    protected function getTokenResponse($callbackData, $baseParameters)
    {
        return $this->http()->call(
            'POST',
            'https://api.instagram.com/oauth/access_token',
            array(),
            http_build_query($baseParameters)
        );
    }

    public function type()
    {
        return 'instagram';
    }
}
