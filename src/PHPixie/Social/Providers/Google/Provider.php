<?php

namespace PHPixie\Social\Providers\Google;

class Provider extends \PHPixie\Social\OAuth\OAuth2\Provider
{
    protected $loginDataEndpoint = 'people/me';

    protected function endpointUrl($endpoint)
    {
        $version = $this->configData->get('apiVersion', '1');
        return 'https://www.googleapis.com/plus/v'.$version.'/'.$endpoint;
    }

    public function loginUrl($callbackUrl, $additionalScope = array())
    {
        $scope = array_merge(
            $this->configData->get('scope', array()),
            $additionalScope
        );

        $scope[]='profile';

        return $this->buildLoginUrl(
            'https://accounts.google.com/o/oauth2/v2/auth',
            $callbackUrl,
            array(
                'scope'         => implode(',', $scope),
                'response_type' => 'code'
            )
        );
    }

    protected function getTokenResponse($callbackData, $baseParameters)
    {
        $baseParameters['grant_type'] = 'authorization_code';

        return $this->http()->call(
            'POST',
            'https://www.googleapis.com/oauth2/v4/token',
            $baseParameters
        );
    }

    public function type()
    {
        return 'google';
    }
}
