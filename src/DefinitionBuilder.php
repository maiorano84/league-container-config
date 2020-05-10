<?php

namespace Maiorano\ContainerConfig;

use League\Container\Definition\DefinitionAggregateInterface;
use League\Container\Definition\DefinitionInterface;

/**
 * Class DefinitionBuilder.
 */
final class DefinitionBuilder implements BuilderInterface
{
    /**
     * @var DefinitionAggregateInterface
     */
    private DefinitionAggregateInterface $definitions;

    /**
     * DefinitionBuilder constructor.
     *
     * @param DefinitionAggregateInterface $definitions
     */
    public function __construct(DefinitionAggregateInterface $definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * @param array $config
     *
     * @return DefinitionAggregateInterface
     */
    public function build(array $config): DefinitionAggregateInterface
    {
        foreach ($config as $key => $value) {
            $this->buildDefinition((string) $key, $value);
        }

        return $this->definitions;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return DefinitionInterface
     */
    public function buildDefinition(string $key, $value): DefinitionInterface
    {
        $concrete = $this->resolveConcrete($value);
        $alias = $this->resolveAlias($key, $value, $concrete);

        if ($concrete instanceof DefinitionInterface) {
            return $this->definitions->add($alias, $concrete, $concrete->isShared());
        }

        $definition = $this->definitions->add($alias, $concrete, $this->resolveShared($value));
        if (is_array($value)) {
            $this->resolveArguments($definition, $value['arguments'] ?? []);
            $this->resolveMethods($definition, $value['methods'] ?? []);
            $this->resolveTags($definition, $value['tags'] ?? []);
        }

        return $definition;
    }

    /**
     * @param mixed $value
     *
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
     * @param mixed  $value
     * @param mixed  $concrete
     *
     * @return string
     */
    private function resolveAlias(string $key, $value, $concrete): string
    {
        if (is_array($value) && isset($value['alias'])) {
            return $value['alias'];
        }
        if (is_numeric($key)) {
            return $this->resolveAliasFromConcrete($key, $concrete);
        }

        return $key;
    }

    /**
     * @param string $key
     * @param mixed  $concrete
     *
     * @return string
     */
    private function resolveAliasFromConcrete(string $key, $concrete): string
    {
        if ($concrete instanceof DefinitionInterface) {
            return $concrete->getAlias();
        }
        if (is_string($concrete) && (class_exists($concrete) || interface_exists($concrete))) {
            return $concrete;
        }

        return is_object($concrete) ? get_class($concrete) : $key;
    }

    /**
     * @param mixed $value
     *
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
     * @param array               $arguments
     *
     * @return DefinitionInterface
     */
    private function resolveArguments(DefinitionInterface $definition, array $arguments): DefinitionInterface
    {
        return $definition->addArguments($arguments);
    }

    /**
     * @param DefinitionInterface $definition
     * @param array               $methods
     *
     * @return DefinitionInterface
     */
    private function resolveMethods(DefinitionInterface $definition, array $methods): DefinitionInterface
    {
        return $definition->addMethodCalls($methods);
    }

    /**
     * @param DefinitionInterface $definition
     * @param array               $tags
     *
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
