<?php

namespace PHPixie\Social\OAuth;

abstract class Token
{
    protected $providerName;
    protected $userId;
    protected $expiresAt;
    protected $userData;

    public function __construct($providerName, $userId, $expiresAt)
    {
        $this->providerName = $providerName;
        $this->userId = $userId;
        $this->expiresAt = $expiresAt;
    }

    public function providerName()
    {
        return $this->providerName;
    }

    public function userId()
    {
        return $this->userId;
    }

    public function isExpired()
    {
        if($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt - time() < 5;
    }
}
