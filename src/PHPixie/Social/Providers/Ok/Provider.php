<?php

namespace PHPixie\Social\Providers\OK;

class Provider extends \PHPixie\Social\OAuth\OAuth2\Provider
{

    protected function endpointUrl($url = NULL)
    {
        return "https://api.ok.ru/fb.do";
    }

    public function loginUrl($callbackUrl, $additionalScope = array())
    {
        return $this->buildLoginUrl(
            'https://connect.ok.ru/oauth/authorize',
            $callbackUrl,
            array(
                'scope'         => implode(',', $this->configData->get('scope', array())),
                'response_type' => 'code',
            )
        );
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
            NULL,
            array(
                "application_key" => $this->configData->getRequired('appPublic'),
                "format"    => "json",
                "method"    => "users.getCurrentUser",
                "sig"       => md5("application_key=". $this->configData->getRequired('appPublic') ."format=jsonmethod=users.getCurrentUser" . md5($tokenData->access_token . $this->configData->getRequired('appSecret')))
            )
        );

        $loginData = $this->normalizeLoginData($loginData, $tokenData);

        $token = $this->buildToken($tokenData, $loginData);

        return $this->user($token, $loginData);
    }

    protected function getTokenResponse($callbackData, $baseParameters)
    {
        $baseParameters['grant_type'] = 'authorization_code';

        return $this->http()->call(
            'POST',
            'https://api.ok.ru/oauth/token.do',
            array(),
            http_build_query($baseParameters)
        );
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
        return 'ok';
    }
}
