<?php

namespace Maiorano\ContainerConfig\Tests;

use League\Container\Definition\DefinitionAggregateInterface;
use League\Container\Definition\DefinitionInterface;
use Maiorano\ContainerConfig\DefinitionBuilder;
use PHPUnit\Framework\TestCase;

class DefinitionBuilderTest extends TestCase
{
    private DefinitionInterface $definition;
    private DefinitionBuilder $builder;
    private DefinitionAggregateInterface $aggregate;

    public function setUp(): void
    {
        $this->definition = $this->createMock(DefinitionInterface::class);
        $this->aggregate = $this->createMock(DefinitionAggregateInterface::class);
        $this->builder = new DefinitionBuilder($this->aggregate);
    }

    /**
     * @param array $config
     * @param array $expectedValues
     * @dataProvider definitionConfigDataProvider
     */
    public function testBuild(array $config, array $expectedValues)
    {
        $this->aggregate
            ->expects($this->exactly(count($config)))
            ->method('add')
            ->withConsecutive(...$expectedValues)
            ->willReturn($this->definition);

        $this->assertInstanceOf(DefinitionAggregateInterface::class, $this->builder->build($config));
    }

    public function testBuildDefinition()
    {
        $this->definition
            ->expects($this->once())
            ->method('isShared')
            ->willReturn(false);

        $this->aggregate
            ->expects($this->once())
            ->method('add')
            ->with('alias', $this->definition, false)
            ->willReturn($this->definition);

        $this->assertInstanceOf(
            DefinitionInterface::class,
            $this->builder->buildDefinition('alias', $this->definition)
        );
    }

    public function testBuildNumericDefinition()
    {
        $this->definition
            ->expects($this->once())
            ->method('isShared')
            ->willReturn(false);

        $this->definition
            ->expects($this->once())
            ->method('getAlias')
            ->willReturn('definitionAlias');

        $this->aggregate
            ->expects($this->once())
            ->method('add')
            ->with('definitionAlias', $this->definition, false)
            ->willReturn($this->definition);

        $this->assertInstanceOf(
            DefinitionInterface::class,
            $this->builder->buildDefinition('0', $this->definition)
        );
    }

    public function testBuildCompleteArrayDefinition()
    {
        $this->definition
            ->expects($this->once())
            ->method('addArguments')
            ->with(['arg1', 'arg2', 'arg3'])
            ->willReturn($this->definition);

        $this->definition
            ->expects($this->once())
            ->method('addMethodCalls')
            ->with(['method' => ['arg1', 'arg2']])
            ->willReturn($this->definition);

        $this->definition
            ->expects($this->exactly(3))
            ->method('addTag')
            ->withConsecutive(['tag1'], ['tag2'], ['tag3'])
            ->willReturn($this->definition);

        $this->aggregate
            ->expects($this->once())
            ->method('add')
            ->with('actualAlias', 'value', true)
            ->willReturn($this->definition);

        $this->assertInstanceOf(
            DefinitionInterface::class,
            $this->builder->buildDefinition('alias', [
                'alias'     => 'actualAlias',
                'concrete'  => 'value',
                'shared'    => true,
                'arguments' => ['arg1', 'arg2', 'arg3'],
                'methods'   => ['method' => ['arg1', 'arg2']],
                'tags'      => ['tag1', 'tag2', 'tag3'],
            ])
        );
    }

    public function definitionConfigDataProvider(): array
    {
        $concreteBuilder = new DefinitionBuilder(
            $this->createMock(DefinitionAggregateInterface::class)
        );

        return [
            [
                'config' => [
                    'alias'  => 'value',
                    'alias2' => [],
                    'alias3' => [
                        'alias'    => 'alias4',
                        'concrete' => 'value',
                        'shared'   => true,
                    ],
                ],
                'expectedValues' => [
                    ['alias', 'value', false],
                    ['alias2', [], false],
                    ['alias4', 'value', true],
                ],
            ],
            [
                'config' => [
                    'value',
                    [],
                    [
                        'alias'    => 'alias4',
                        'concrete' => 'value',
                        'shared'   => true,
                    ],
                    DefinitionInterface::class,
                    $concreteBuilder,
                ],
                'expectedValues' => [
                    ['0', 'value', false],
                    ['1', [], false],
                    ['alias4', 'value', true],
                    [DefinitionInterface::class, DefinitionInterface::class, false],
                    [DefinitionBuilder::class, $concreteBuilder, false],
                ],
            ],
        ];
    }
}
