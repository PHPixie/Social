<?php

namespace PHPixie\Social\OAuth;

abstract class Provider extends \PHPixie\Social\Provider
{
    abstract public function loginUrl($callbackUrl);
    abstract public function handleCallback($callbackUrl, $callbackData);

    protected function http()
    {
        return $this->builder->http();
    }

    protected function format()
    {
        return $this->builder->format();
    }

    protected function user($token, $loginData = null)
    {
        return $this->builder->oauthUser($this, $token, $loginData);
    }

    public function get($token, $endpoint, $query = array())
    {
        return $this->api($token, 'GET', $endpoint, $query, null);
    }

    public function post($token, $endpoint, $data = null, $query = array())
    {
        return $this->api($token, 'POST', $endpoint, $query, $data);
    }

    abstract public function api($token, $method, $endpoint, $query = array(), $data = null);
}
