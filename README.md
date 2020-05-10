# League Container Builder

A simple Builder pattern to create a complete [league/container](https://github.com/thephpleague/container) instance out of formatted arrays.

[![Author](http://img.shields.io/badge/author-Matt%20Maiorano-blue.svg?style=flat-square)](https://mattmaiorano.com)
[![Latest Stable Version](https://poser.pugx.org/maiorano84/league-container-config/v/stable)](https://packagist.org/packages/maiorano84/league-container-config)
[![Total Downloads](https://poser.pugx.org/maiorano84/league-container-config/downloads)](https://packagist.org/packages/maiorano84/league-container-config)
[![License](https://poser.pugx.org/maiorano84/league-container-config/license)](https://packagist.org/packages/maiorano84/league-container-config)
[![Build Status](https://travis-ci.com/maiorano84/league-container-config.svg?branch=master)](https://travis-ci.com/maiorano84/league-container-config)
[![Code Coverage](https://scrutinizer-ci.com/g/maiorano84/league-container-config/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/maiorano84/league-container-config/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/maiorano84/league-container-config/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/maiorano84/league-container-config/?branch=master)
[![StyleCI](https://github.styleci.io/repos/262726222/shield?branch=master)](https://github.styleci.io/repos/262726222)

## Requirements
League Container Builder requires PHP 7.4 or greater.

## Composer
This package may be installed as a Composer dependency by running the following command:

`composer require maiorano84/league-container-config`

If you would like to use the latest unstable version, you can run:

`composer require maiorano84/league-container-config:dev-master`

## Usage

This is a small addon to [The PHP League's Container](https://container.thephpleague.com/) package, which allows for passing in configuration objects and receiving complete Container instances in your application.

There are 4 Builders available for use:

* **ContainerBuilder** - The primary builder used both for instantiating its Builder dependencies as well as constructing the Container instance
* **DefinitionBuilder** - Responsible for constructing Definition instances and adding them to its internal DefinitionAggregate
* **InflectorBuilder** - Responsible for constructing Inflector instances and adding them to its internal InflectorAggregate
* **ServiceProviderBuilder** - Responsible for constructing ServiceProvider instances and adding them to its internal ServiceProviderAggregate

### The Container Builder

It is recommended to instantiate the Container Builder with the `make` factory method:

```
use Maiorano\ContainerConfig\ContainerBuilder;

$builder = ContainerBuilder::make();
```

From here, generating Container objects can be done with the `build` command:

```
use League\Container\ReflectionContainer;
$container = $builder->build([
    'definitions' => [
        AbstractInterface::class => Concrete::class, // Simple Mapping
        'name' => [ // Complete Definition structure
            'alias' => AbstractInterface::class,
            'concrete' => Concrete::class,
            'shared' => true,
            'arguments' => [Dependency::class, 'arg2', 'arg3'],
            'methods'   => ['methodName' => ['arg1', 'arg2']],
            'tags'      => ['tag1', 'tag2', 'tag3'],
        ],
    ],
    'serviceProviders' => [
        CustomServiceProvider::class,
        new UserDefinedServiceProvider,
    ],
    'inflectors' => [
        DefinitionAlias::class => 'methodName', // Simple inflector
        OtherDefinitionAlias::class => [ // Complete Inflector Structure
            'properties' => ['propery' => 'value'],
            'methods' => ['methodName' => ['arg1', 'arg2']],
        ],
    ],
    'delegates' => [
        $anotherContainerObject,
        ReflectionContainer::class,
    ],
]);
```

In this example, each top-level configuration block is passed down to its corresponding builder.

### The Definition Builder

In the event you need to use each builder separately, a DefinitionBuilder only requires an object that fulfills the DefinitionAggregateInterface. Definitions may be built in bulk or one at a time.

Generating definitions in bulk using `build`:

```
use Maiorano\ContainerConfig\DefinitionBuilder;
use League\Container\Definition\DefinitionAggregate;

$builder = new DefinitionBuilder(new DefinitionAggregate);
$aggregate = $builder->build([
    AbstractInterface::class => Concrete::class, // Simple Mapping
    'name' => [ // Complete Definition structure
        'alias' => AbstractInterface::class,
        'concrete' => Concrete::class,
        'shared' => true,
        'arguments' => [Dependency::class, 'arg2', 'arg3'],
        'methods'   => ['methodName' => ['arg1', 'arg2']],
        'tags'      => ['tag1', 'tag2', 'tag3'],
    ],
]);
```

#### Definition aliasing

While it is always recommended to define your aliases using array keys or the 'alias' mapping, the Definition Builder will also do its best to resolve an alias for you in case it's not provided.

Definition aliases are resolved in the following order:

1. Does the configuration value contain an inner 'alias' key? If so, use it.
2. Is the configuration key non-numeric? If so, use it.
3. Is the provided concrete value a string that represents a valid class or interface? If so, use it.
4. Is the provided concrete value an object? If so, generate an alias using `get_class`
5. If none of the above are true, use the provided key as an alias.

In the unlikely case that a concrete is provided that implements `League\Container\Definition\DefinitionInterface`, then the builder will assume that the user intends to provide their own definition and will circumvent its own build process in favor of the user's implementation.

In this case, aliases are resolved in the following way:

1. Does the configuration value contain an inner 'alias' key? If so, use it.
2. Is the configuration key non-numeric? If so, use it.
3. If none of the above are true, use the existing definition alias.

### The Inflector Builder

Coming soon...

### The Service Provider Builder

Coming soon...
