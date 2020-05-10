<?php

namespace Maiorano\ContainerConfig\Tests;

use League\Container\Container;
use League\Container\ReflectionContainer;
use Maiorano\ContainerConfig\BuilderInterface;
use Maiorano\ContainerConfig\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContainerBuilderTest extends TestCase
{
    private ContainerBuilder $builder;

    public function setUp(): void
    {
        $aggregateBuilder = $this->createMock(BuilderInterface::class);
        $this->builder = new ContainerBuilder($aggregateBuilder, $aggregateBuilder, $aggregateBuilder);
    }

    public function testMake()
    {
        $this->assertInstanceOf(ContainerBuilder::class, ContainerBuilder::make());
    }

    public function testBuild()
    {
        $this->assertInstanceOf(Container::class, $this->builder->build([]));
    }

    public function testLoadDelegates()
    {
        $container = $this->createMock(Container::class);
        $container
            ->expects($this->exactly(2))
            ->method('delegate');

        $this->assertInstanceOf(Container::class, $this->builder->loadDelegates($container, [
            ReflectionContainer::class,
            $this->createMock(ContainerInterface::class),
        ]));
    }
}
