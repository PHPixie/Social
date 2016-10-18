<?php

namespace PHPixie\Social\Providers\Github;

/**
 * Class Provider
 *
 * @package PHPixie\Social\Providers\GitHub
 *
 * @from https://github.com/PHPixie/Social/pull/2
 */

class Provider extends \PHPixie\Social\OAuth\OAuth2\Provider
{
    /**
     * @var string The user endpoint for this provider. It will be passed to the endpointUrl method.
     */
    protected $loginDataEndpoint = 'user';

    protected function endpointUrl($endpoint)
    {
        return 'https://api.github.com/' . $endpoint;
    }

    /**
     * @param       $callbackUrl     The URL in the application where users will be sent after the authentication.
     *                               The one specified in the OAuth Application settings page on GitHub, must be the
     *                               same as the one in the loginCallback property in the app's routeResolver.php
     *                               config file.
     *
     * @param array $additionalScope Additional scopes that will be requested to the user
     *
     * @return mixed The url for github.com to authorize this application
     */
    public function loginUrl($callbackUrl, $additionalScope = array())
    {
        $scope = array_merge(
            $this->configData->get('scope', array()),
            $additionalScope
        );

        return $this->buildLoginUrl(
            'https://github.com/login/oauth/authorize',
            $callbackUrl,
            array('scope' => implode(',', $scope))
        );
    }

    /**
     * The method is called to obtain the access token, useful for the requests to the API.
     * The Accept header is specified, because GitHub returns a string by default. JSON is needed.
     *
     * @param $callbackData   Data that are returned from the provider
     * @param $baseParameters Array. Contains: client_id, client_secret, redirect_uri and code. Those parameters are
     *                        necessaries for obtaining the token.
     *
     * @return mixed The token response from the API, based on the parameters of baseParameters param.
     */
    protected function getTokenResponse($callbackData, $baseParameters)
    {
        return $this->http()->call(
            'POST',
            'https://github.com/login/oauth/access_token',
            $baseParameters,
            null,
            array('Accept' => 'application/json')
        );
    }

    /**
     * Makes a call to the GitHub API.
     * It's overriden because GitHub needs an User-Agent header. The value of the User-Agent header is taken from the
     * social config file.
     * The version is needed to make a call to a specific API version. It's also added to the social.php file.
     *
     * @return mixed The json from the API
     * @see https://developer.github.com/v3/#user-agent-required
     */
    protected function apiCall($accessToken, $method, $endpoint, $query = array(), $data = null)
    {
        /**
         * @url https://github.com/OAI/OpenAPI-Specification/blob/master/fixtures/v2.0/json/resources/multipleMimeTypes.json#L23
         */
        $format    = $this->configData->get('format', '+json');
        $userAgent = $this->configData->get('userAgent', 'PHPixie Social Component');
        $version   = $this->configData->get('version', 3);

        $url                   = $this->endpointUrl($endpoint);
        $query['access_token'] = $accessToken;
        $response              = $this->http()->call($method, $url, $query, $data,
            array(
                'User-Agent' => $userAgent,
                'Accept'     => 'application/vnd.github.' . $version . $format,
            )
        );

        return $this->decodeApiResponse($response);
    }

    protected function buildToken($tokenData, $loginData)
    {
        $expiresIn    = $this->configData->get('expiresIn');

        return $this->token(
            $loginData->id,
            $tokenData->access_token,
            $expiresIn
        );
    }

    public function type()
    {
        return 'github';
    }
}