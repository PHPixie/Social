<?php

namespace PHPixie\Social\Providers\VK;

class Provider extends \PHPixie\Social\OAuth\OAuth2\Provider
{
    protected $loginDataEndpoint = 'users.get';

    protected function endpointUrl($endpoint)
    {
        return 'https://api.vk.com/method/'.$endpoint;
    }

    public function loginUrl($callbackUrl, $additionalScope = array())
    {
        $scope = array_merge(
            $this->configData->get('scope', array()),
            $additionalScope
        );

        return $this->buildLoginUrl(
            'https://oauth.vk.com/authorize',
            $callbackUrl,
            array(
                'scope'         => implode(',', $scope),
                'response_type' => 'code',
                'v'             => $this->configData->get('apiVersion', '5.52'),
                'display'       => $this->configData->get('display', 'page'),
            )
        );
    }

    protected function getTokenResponse($callbackData, $baseParameters)
    {
        return $this->http()->call(
            'GET',
            'https://oauth.vk.com/access_token',
            $baseParameters
        );
    }

    protected function normalizeLoginData($loginData, $tokenData)
    {
        $loginData = $loginData[0];

        if(isset($tokenData->email)) {
            $loginData->email = $tokenData->email;
        }

        return $loginData;
    }

    public function api($token, $method, $endpoint, $query = array(), $data = null)
    {
        $query['version'] = $this->configData->get('apiVersion', '5.52');
        $accessToken = $token->accessToken();
        return $this->apiCall($accessToken, $method, $endpoint, $query, $data);
    }

    public function apiCall($accessToken, $method, $endpoint, $query = array(), $data = null)
    {
        $response = parent::apiCall($accessToken, $method, $endpoint, $query, $data);

        if(isset($response->error)) {
            $error = $response->error;
            throw new \PHPixie\Social\Exception("VK api error: {$error->error_msg}, code: $error->error_code");
        }

        return $response->response;
    }

    protected function getUserId($loginData)
    {
        return $loginData->uid;
    }

    protected function buildToken($tokenData, $loginData)
    {
        return $this->token(
            $loginData->uid,
            $tokenData->access_token,
            $tokenData->expires_in
        );
    }

    public function type()
    {
        return 'vk';
    }
}
