<?php

namespace Maiorano\ContainerConfig;

/**
 * Interface BuilderInterface.
 */
interface BuilderInterface
{
    /**
     * @param array $config
     *
     * @return mixed
     */
    public function build(array $config);
}
