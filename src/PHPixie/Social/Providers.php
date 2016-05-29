<?php

namespace PHPixie\Social;

class Providers
{
    protected $builder;
    protected $configData;

    protected $providers = array();

    public function __construct($builder, $configData)
    {
        $this->builder = $builder;
        $this->configData = $configData;
    }

    public function get($name)
    {
        if(!isset($this->providers[$name])) {
            $providerConfig = $this->configData->slice($name);
            $type = $providerConfig->getRequired('type');
            $this->providers[$name] = $this->builder->buildProvider($type, $name, $providerConfig);
        }

        return $this->providers[$name];
    }
}
