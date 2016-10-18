<?php

namespace PHPixie\Social\OAuth\OAuth2;

class Token extends \PHPixie\Social\OAuth\Token
{
    protected $accessToken;

    public function __construct($providerName, $userId, $accessToken, $expiresIn = null)
    {
        $expiresAt = null;
        if ($expiresIn !== null) {
            $expiresAt = time() + $expiresIn;
        }
        parent::__construct($providerName, $userId, $expiresAt);
        $this->accessToken = $accessToken;
    }

    public function accessToken()
    {
        return $this->accessToken;
    }
}
