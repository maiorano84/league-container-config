<?php

namespace Maiorano\ContainerConfig\Tests;

use League\Container\ServiceProvider\ServiceProviderAggregateInterface;
use Maiorano\ContainerConfig\ServiceProviderBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Service\ServiceProviderInterface;

class ServiceProviderBuilderTest extends TestCase
{
    private ServiceProviderBuilder $builder;
    private ServiceProviderAggregateInterface $aggregate;

    public function setUp(): void
    {
        $this->aggregate = $this->createMock(ServiceProviderAggregateInterface::class);
        $this->builder = new ServiceProviderBuilder($this->aggregate);
    }

    /**
     * @param $serviceProvider
     * @dataProvider serviceProviderDataProvider
     */
    public function testBuildServiceProvider($serviceProvider): void
    {
        $this->aggregate
            ->expects($this->once())
            ->method('add')
            ->with($serviceProvider)
            ->willReturn($this->aggregate);

        $this->assertInstanceOf(
            ServiceProviderAggregateInterface::class,
            $this->builder->buildServiceProvider($serviceProvider)
        );
    }

    public function testBuild(): void
    {
        $this->aggregate
            ->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                ['ServiceProviderClass'],
                [ServiceProviderInterface::class]
            )
            ->willReturn($this->aggregate);

        $this->assertInstanceOf(
            ServiceProviderAggregateInterface::class,
            $this->builder->build([
                'ServiceProviderClass',
                ServiceProviderInterface::class,
            ])
        );
    }

    public function serviceProviderDataProvider(): array
    {
        return [
            ['ServiceProviderClass'],
            [$this->createMock(ServiceProviderInterface::class)],
        ];
    }
}
