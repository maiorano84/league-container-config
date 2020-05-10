<?php

namespace Maiorano\ContainerConfig;

use League\Container\Inflector\InflectorAggregateInterface;
use League\Container\Inflector\InflectorInterface;

/**
 * Class InflectorBuilder
 * @package Maiorano\ContainerConfig
 */
final class InflectorBuilder implements BuilderInterface
{
    /**
     * @var InflectorAggregateInterface
     */
    private InflectorAggregateInterface $inflectors;

    /**
     * InflectorBuilder constructor.
     * @param InflectorAggregateInterface $inflectors
     */
    public function __construct(InflectorAggregateInterface $inflectors)
    {
        $this->inflectors = $inflectors;
    }

    /**
     * @param array $config
     * @return InflectorAggregateInterface
     */
    public function build(array $config): InflectorAggregateInterface
    {
        foreach ($config as $key => $value) {
            $this->buildInflector((string)$key, $value);
        }
        return $this->inflectors;
    }

    /**
     * @param string $key
     * @param string|array|callable $value
     * @return InflectorInterface
     */
    public function buildInflector(string $key, $value): InflectorInterface
    {
        $inflector = $this->inflectors->add($key, $this->resolveCallback($value));

        if (is_string($value)) {
            $inflector->invokeMethod($value, []);
        }
        if (is_array($value) && !is_callable($value)) {
            $this->resolveProperties($inflector, $value['properties'] ?? []);
            $this->resolveMethods($inflector, $value['methods'] ?? []);
        }
        return $inflector;
    }

    /**
     * @param mixed $value
     * @return callable|null
     */
    private function resolveCallback($value): ?callable
    {
        if (is_callable($value)) {
            return $value;
        } else if (!is_array($value)) {
            return null;
        }
        return $value['callback'] ?? null;
    }

    /**
     * @param InflectorInterface $inflector
     * @param array $properties
     * @return InflectorInterface
     */
    private function resolveProperties(InflectorInterface $inflector, array $properties): InflectorInterface
    {
        return $inflector->setProperties($properties);
    }

    /**
     * @param InflectorInterface $inflector
     * @param array $methods
     * @return InflectorInterface
     */
    private function resolveMethods(InflectorInterface $inflector, array $methods): InflectorInterface
    {
        return $inflector->invokeMethods($methods);
    }
}
