<?php

namespace Maiorano\ContainerConfig;

/**
 * Interface BuilderInterface
 * @package Maiorano\ContainerConfig
 */
interface BuilderInterface
{
    /**
     * @param array $config
     * @return mixed
     */
    public function build(array $config);
}
