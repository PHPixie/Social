<?php

namespace PHPixie;

class Social
{
    protected $builder;
    protected $configData;

    public function __construct($configData)
    {
        $this->builder = $this->buildBuilder(
            $configData
        );
    }

    public function providers()
    {
        return $this->builder->providers();
    }

    public function provider($name)
    {
        return $this->builder->providers()->get($name);
    }

    public function builder()
    {
        return $this->builder;
    }

    protected function buildBuilder($configData)
    {
        return new Social\Builder($configData);
    }
}
