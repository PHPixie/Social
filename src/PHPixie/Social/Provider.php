<?php

namespace PHPixie\Social;

abstract class Provider
{
    protected $builder;
    protected $name;
    protected $configData;

    public function __construct($builder, $name, $configData)
    {
        $this->builder   = $builder;
        $this->name   = $name;
        $this->configData = $configData;
    }

    public function name()
    {
        return $this->name;
    }

    abstract public function type();
}
