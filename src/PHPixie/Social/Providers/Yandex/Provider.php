<?php

namespace PHPixie\Social\Providers\Yandex;

class Provider extends \PHPixie\Social\OAuth\OAuth2\Provider
{

    protected function endpointUrl($endpoint=NULL)
    {
        return 'https://login.yandex.ru/info';
    }

    public function loginUrl($callbackUrl, $additionalScope = array())
    {
        $scope = array_merge(
            $this->configData->get('scope', array()),
            $additionalScope
        );

        return $this->buildLoginUrl(
            'https://oauth.yandex.ru/authorize',
            $callbackUrl,
            array(
                'response_type' => 'code',
            )
        );
    }

    protected function apiCall($accessToken, $method, $endpoint, $query = array(), $data = null)
    {
        $url = $this->endpointUrl();
        $query['oauth_token'] = $accessToken;

        $response = $this->http()->call($method, $url, $query, $data);
        return $this->decodeApiResponse($response);
    }

    protected function getTokenResponse($callbackData, $baseParameters)
    {
        $baseParameters['grant_type'] = 'authorization_code';

        return $this->http()->call(
            'POST',
            'https://oauth.yandex.ru/token',
            array(),
            http_build_query($baseParameters)
        );
    }

    protected function buildToken($tokenData, $loginData)
    {
        return $this->token(
            $loginData->id,
            $tokenData->access_token,
            $tokenData->expires_in
        );
    }

    public function type()
    {
        return 'yandex';
    }
}
