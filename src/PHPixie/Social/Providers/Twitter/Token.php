<?php

namespace PHPixie\Social\Providers\Twitter;

class Token extends \PHPixie\Social\OAuth\Token
{
    protected $oauthToken;
    protected $oauthSecret;

    public function __construct($providerName, $userId, $oauthToken, $oauthSecret)
    {
        parent::__construct($providerName, $userId, null);
        $this->oauthToken = $oauthToken;
        $this->oauthSecret = $oauthSecret;
    }

    public function oauthToken()
    {
        return $this->oauthToken;
    }

    public function oauthSecret()
    {
        return $this->oauthSecret;
    }
}
