<?php

namespace PHPixie\Social;

abstract class User
{
    protected $provider;

    public function __construct($provider)
    {
        $this->provider = $provider;
    }

    public function provider()
    {
        return $this->provider;
    }

    public function providerName()
    {
        return $this->provider->name();
    }

    abstract public function id();
}
