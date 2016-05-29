<?php

namespace PHPixie\Social\OAuth;

class User extends \PHPixie\Social\User
{
    protected $token;
    protected $loginData;

    public function __construct($provider, $token, $loginData = null)
    {
        $this->provider = $provider;
        $this->token = $token;
        $this->loginData = $loginData;
    }

    public function token()
    {
        return $this->token;
    }

    public function loginData()
    {
        return $this->loginData;
    }

    public function id()
    {
        return $this->token->userId();
    }

    public function api($method, $endpoint, $query = array(), $data = null)
    {
        return $this->provider->api($this->token, $method, $endpoint, $query, $data);
    }

    public function get($endpoint, $query = array())
    {
        return $this->provider->get($this->token, $endpoint, $query);
    }

    public function post($endpoint, $data = null, $query = array())
    {
        return $this->provider->post($this->token, $endpoint, $data, $query);
    }
}
