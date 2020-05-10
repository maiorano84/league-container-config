<?php

namespace Maiorano\ContainerConfig\Tests;

use League\Container\Inflector\Inflector;
use League\Container\Inflector\InflectorAggregateInterface;
use League\Container\Inflector\InflectorInterface;
use Maiorano\ContainerConfig\InflectorBuilder;
use PHPUnit\Framework\TestCase;

class InflectorBuilderTest extends TestCase
{
    private InflectorBuilder $builder;
    private InflectorInterface $inflector;
    private InflectorAggregateInterface $aggregate;

    public function setUp(): void
    {
        $this->inflector = $this->createMock(Inflector::class);
        $this->aggregate = $this->createMock(InflectorAggregateInterface::class);
        $this->builder = new InflectorBuilder($this->aggregate);
    }

    /**
     * @param $key
     * @param $value
     * @param $expectedCallback
     * @param $methods
     * @param $properties
     * @dataProvider inflectorDataProvider
     */
    public function testBuildInflector($key, $value, $expectedCallback, $methods, $properties): void
    {

        $this->aggregate
            ->expects($this->once())
            ->method('add')
            ->with($key, $expectedCallback)
            ->willReturn($this->inflector);

        if (is_string($value)) {
            $this->inflector
                ->expects($this->once())
                ->method('invokeMethod')
                ->with($value);
        }

        if ($methods) {
            $this->inflector
                ->expects($this->once())
                ->method('invokeMethods')
                ->with($methods);
        }

        if ($properties) {
            $this->inflector
                ->expects($this->once())
                ->method('setProperties')
                ->with($properties);
        }

        $this->builder->buildInflector($key, $value);
    }

    public function testBuild(): void
    {
        $cb = function () {
        };
        $this->aggregate
            ->expects($this->once())
            ->method('add')
            ->with('test', $cb)
            ->willReturn($this->inflector);

        $this->inflector
            ->expects($this->once())
            ->method('invokeMethods')
            ->with(['method' => 'args']);

        $this->inflector
            ->expects($this->once())
            ->method('setProperties')
            ->with(['prop' => 'value']);

        $this->builder->build([
            'test' => [
                'callback' => $cb,
                'methods' => ['method' => 'args'],
                'properties' => ['prop' => 'value']
            ]
        ]);
    }

    public function inflectorDataProvider(): array
    {
        $closure = function () {
        };
        return [
            ['key', $closure, $closure, null, null],
            ['key', ['callback' => $closure], $closure, null, null],
            ['key', 'method', null, null, null],
            ['key', [
                'callback' => $closure,
                'methods' => ['method' => 'args'],
                'properties' => ['prop' => 'val'],
            ], $closure, ['method' => 'args'], ['prop' => 'val']]
        ];
    }
}
