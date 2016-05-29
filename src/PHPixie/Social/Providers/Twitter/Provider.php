<?php

namespace PHPixie\Social\Providers\Twitter;

class Provider extends \PHPixie\Social\OAuth\Provider
{
    public function loginUrl($callbackUrl)
    {
        $url = $this->apiUrl('oauth/request_token');

        $headers = array(
            'Authorization' => $this->getAuthHeader('POST', $url, array(), null, null, $callbackUrl)
        );
        $response = $this->http()->call('POST', $url, array(), array(), $headers);
        $response = $this->format()->queryDecode($response);

        return $this->http()->buildUrl(
            'https://api.twitter.com/oauth/authenticate',
            array('oauth_token' => $response->oauth_token)
        );
    }

    public function handleCallback($callbackUrl, $callbackData)
    {
        $format = $this->format();
        $oauthToken = $callbackData->getRequired('oauth_token');
        $oauthVerifier = $callbackData->getRequired('oauth_verifier');

        $tokenResponse = $this->apiCall(
            'POST',
            'oauth/access_token',
            $oauthToken,
            $oauthToken,
            array(),
            array('oauth_verifier' => $oauthVerifier)
        );
        $tokenResponse = $format->queryDecode($tokenResponse);

        $userResponse = $this->apiCall(
            'GET',
            $this->endpointUrl('account/verify_credentials'),
            $tokenResponse->oauth_token,
            $tokenResponse->oauth_token_secret
        );

        $userResponse = $format->jsonDecode($userResponse);

        $token = $this->token(
            $userResponse->id,
            $tokenResponse->oauth_token,
            $tokenResponse->oauth_token_secret
        );

        return $this->user($token, $userResponse);
    }

    protected function getAuthHeader($method, $url, $parameters = array(), $token, $secret, $callback = null)
    {
        $data = array(
            'oauth_consumer_key' => $this->configData->get('consumerKey'),
            'oauth_nonce' => $this->nonce(32),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        );

        if($token !== null) {
            $data['oauth_token'] = $token;
        }

        if($callback !== null) {
            $data['oauth_callback'] = $callback;
        }

        $signData = array_merge($data, $parameters);
        ksort($signData);

        $signData = http_build_query($signData);

        $signData = strtoupper($method).'&'.urlencode($url).'&'.urlencode($signData);

        $consumerSecret = $this->configData->get('consumerSecret');

        if($secret == null) {
            $secret = $token;
        }
        $signKey = urlencode($consumerSecret).'&'.urlencode($secret);

        $data['oauth_signature'] = base64_encode(hash_hmac('sha1', $signData, $signKey, true));

        $header = 'OAuth ';

        $header = array();
        foreach($data as $key => $value) {
            $header[] = urlencode($key).'="'.urlencode($value).'"';
        }

        $header = 'OAuth '.implode(', ', $header);
        return $header;
    }

    protected function nonce()
    {
        return str_shuffle(substr(uniqid().uniqid(), 0, 32));
    }

    public function api($token, $method, $endpoint, $query = array(), $data = null)
    {
        if($data === null) {
            $data = array();
        }

        $url = $this->endpointUrl($endpoint);

        $result = $this->apiCall(
            $method,
            $url,
            $token->oauthToken(),
            $token->oauthSecret(),
            $query,
            $data
        );

        return $this->format()->jsonDecode($result);
    }

    protected function apiCall($method, $url, $token, $secret, $query = array(), $data = array())
    {
        $url = $this->apiUrl($url);

        $params = array_merge($query, $data);
        $headers = array(
            'Authorization' => $this->getAuthHeader($method, $url, $params, $token, $secret)
        );

        return $this->http()->call($method, $url, $query, $data, $headers);
    }

    protected function endpointUrl($endpoint)
    {
        $version = $this->configData->get('apiVersion', '1.1');
        return $version.'/'.$endpoint.'.json';
    }

    protected function apiUrl($url)
    {
        return 'https://api.twitter.com/'.$url;
    }

    public function type()
    {
        return 'twitter';
    }

    public function token($userId, $oauthToken, $oauthSecret)
    {
        return new Token($this->name, $userId, $oauthToken, $oauthSecret);
    }
}
