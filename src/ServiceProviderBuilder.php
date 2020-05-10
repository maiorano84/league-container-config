<?php

namespace Maiorano\ContainerConfig;

use League\Container\ServiceProvider\ServiceProviderAggregateInterface;
use League\Container\ServiceProvider\ServiceProviderInterface;

/**
 * Class ServiceProviderBuilder
 * @package Maiorano\ContainerConfig
 */
final class ServiceProviderBuilder implements BuilderInterface
{
    /**
     * @var ServiceProviderAggregateInterface
     */
    private ServiceProviderAggregateInterface $serviceProviders;

    /**
     * ServiceProviderBuilder constructor.
     * @param ServiceProviderAggregateInterface $serviceProviders
     */
    public function __construct(ServiceProviderAggregateInterface $serviceProviders)
    {
        $this->serviceProviders = $serviceProviders;
    }

    /**
     * @param array $serviceProviders
     * @return ServiceProviderAggregateInterface
     */
    public function build(array $serviceProviders): ServiceProviderAggregateInterface
    {
        foreach ($serviceProviders as $serviceProvider) {
            $this->buildServiceProvider($serviceProvider);
        }
        return $this->serviceProviders;
    }

    /**
     * @param string|ServiceProviderInterface $serviceProvider
     * @return ServiceProviderAggregateInterface
     */
    public function buildServiceProvider($serviceProvider): ServiceProviderAggregateInterface
    {
        return $this->serviceProviders->add($serviceProvider);
    }
}
