<?php

namespace Maiorano\ContainerConfig;

use League\Container\Container;
use League\Container\Definition\DefinitionAggregate;
use League\Container\Inflector\InflectorAggregate;
use League\Container\ServiceProvider\ServiceProviderAggregate;
use Psr\Container\ContainerInterface;

/**
 * Class ContainerBuilder
 * @package Maiorano\ContainerConfig
 */
final class ContainerBuilder
{
    /**
     * @var BuilderInterface
     */
    private BuilderInterface $definitionBuilder;
    /**
     * @var BuilderInterface
     */
    private BuilderInterface $serviceProviderBuilder;
    /**
     * @var BuilderInterface
     */
    private BuilderInterface $inflectorBuilder;

    /**
     * ContainerBuilder constructor.
     * @param BuilderInterface $definitionBuilder
     * @param BuilderInterface $serviceProviderBuilder
     * @param BuilderInterface $inflectorBuilder
     */
    public function __construct(
        BuilderInterface $definitionBuilder,
        BuilderInterface $serviceProviderBuilder,
        BuilderInterface $inflectorBuilder
    )
    {
        $this->definitionBuilder = $definitionBuilder;
        $this->serviceProviderBuilder = $serviceProviderBuilder;
        $this->inflectorBuilder = $inflectorBuilder;
    }

    /**
     * @return ContainerBuilder
     */
    public static function make(): ContainerBuilder
    {
        return new static(
            new DefinitionBuilder(new DefinitionAggregate),
            new ServiceProviderBuilder(new ServiceProviderAggregate),
            new InflectorBuilder(new InflectorAggregate)
        );
    }

    /**
     * @param array $config
     * @return Container
     */
    public function build(array $config): Container
    {
        $container = new Container(
            $this->definitionBuilder->build($config['definitions'] ?? []),
            $this->serviceProviderBuilder->build($config['serviceProviders'] ?? []),
            $this->inflectorBuilder->build($config['inflectors'] ?? [])
        );
        return $this->loadDelegates($container, $config['delegates'] ?? []);
    }

    /**
     * @param Container $container
     * @param array $delegates
     * @return Container
     */
    public function loadDelegates(Container $container, array $delegates): Container
    {
        foreach ($delegates as $delegate) {
            /**
             * @var ContainerInterface $instance
             */
            $instance = $delegate instanceof ContainerInterface ? $delegate : new $delegate;
            $container->delegate($instance);
        }
        return $container;
    }
}
