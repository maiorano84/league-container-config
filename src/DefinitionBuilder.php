<?php

namespace Maiorano\ContainerConfig;

use League\Container\Definition\DefinitionAggregateInterface;
use League\Container\Definition\DefinitionInterface;

/**
 * Class DefinitionBuilder
 * @package Maiorano\ContainerConfig
 */
final class DefinitionBuilder implements BuilderInterface
{
    /**
     * @var DefinitionAggregateInterface
     */
    private DefinitionAggregateInterface $definitions;

    /**
     * DefinitionBuilder constructor.
     * @param DefinitionAggregateInterface $definitions
     */
    public function __construct(DefinitionAggregateInterface $definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * @param array $config
     * @return DefinitionAggregateInterface
     */
    public function build(array $config): DefinitionAggregateInterface
    {
        foreach ($config as $key => $value) {
            $this->buildDefinition((string)$key, $value);
        }
        return $this->definitions;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return DefinitionInterface
     */
    public function buildDefinition(string $key, $value): DefinitionInterface
    {
        if ($value instanceof DefinitionInterface) {
            $alias = is_numeric($key) ? $value->getAlias() : $key;
            return $this->definitions->add($alias, $value, $value->isShared());
        }

        $concrete = $this->resolveConcrete($value);
        $alias = $this->resolveAlias($key, $value, $concrete);
        $definition = $this->definitions->add($alias, $concrete, $this->resolveShared($value));
        if(is_array($value)) {
            $this->resolveArguments($definition, (array)$value['arguments']);
            $this->resolveMethods($definition, (array)$value['methods']);
            $this->resolveTags($definition, (array)$value['tags']);
        }
        return $definition;
    }

    /**
     * @param mixed $value
     * @return array|mixed|string
     */
    private function resolveConcrete($value)
    {
        if (is_array($value) && isset($value['concrete'])) {
            return $value['concrete'];
        }
        return $value;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param mixed $concrete
     * @return string
     */
    private function resolveAlias(string $key, $value, $concrete): string
    {
        if (is_array($value) && isset($value['alias'])) {
            return $value['alias'];
        }
        if (is_numeric($key)) {
            if (is_string($concrete) && (class_exists($concrete) || interface_exists($concrete))) {
                return $concrete;
            }
            if (is_object($concrete)) {
                return get_class($concrete);
            }
        }
        return $key;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function resolveShared($value): bool
    {
        if (is_array($value) && isset($value['shared'])) {
            return $value['shared'] === true;
        }
        return false;
    }

    /**
     * @param DefinitionInterface $definition
     * @param array $arguments
     * @return DefinitionInterface
     */
    private function resolveArguments(DefinitionInterface $definition, array $arguments): DefinitionInterface
    {
        return $definition->addArguments($arguments);
    }

    /**
     * @param DefinitionInterface $definition
     * @param array $methods
     * @return DefinitionInterface
     */
    private function resolveMethods(DefinitionInterface $definition, array $methods): DefinitionInterface
    {
        return $definition->addMethodCalls($methods);
    }

    /**
     * @param DefinitionInterface $definition
     * @param array $tags
     * @return DefinitionInterface
     */
    private function resolveTags(DefinitionInterface $definition, array $tags): DefinitionInterface
    {
        foreach ($tags as $tag) {
            $definition->addTag($tag);
        }
        return $definition;
    }
}
