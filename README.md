# container

Basic Service Container implementation providing `psr/container-implementation`.

Also known as Dependency Injection Container or Service Locator.

See [PSR-11](https://www.php-fig.org/psr/psr-11/) for more information.

## Use case

### Step 1: define a list of rules to create objects of needed types

```php
<?php

// services.php

namespace Vertilia\Container;

return [
    // instantiating via callback
    EnvMockInterface::class => function () {
        return new EnvMock();
    },

    // instantiating via array of service names
    ServiceMock::class => [EnvMockInterface::class],

    // instantiating via callback with additional params allowed
    'ServiceMockAlias' => function (...$args) {
        return new ServiceMock($this->get(EnvMockInterface::class), ...$args);
    },

    // returning existing object
    'ServiceMockObject' => (object) 'test string',

    // returning string value
    'ServiceMockString' => 'test string',

    // returning other scalar value
    'ServiceMockInt' => 42,
];
```

Here you may use some different formats to specify how objects of specific
classes will be created:

1. instantiating via callback. Defines a callback that will return a new object
   instance. In our example when we'll ask for the EnvMockInterface we will
   receive an instance of EnvMock

2. instantiating via array of service names. If object creation needs a list of
   other objects for its constructor, we provide their names in an array. This
   way container will first instantiate these dependencies and then will call
   the class constructor passing dependencies as parameters. In our case the
   container will proceed in the following way:

   `return new ServiceMock(new EnvMock);` (here `EnvMockInterface::class`
   dependency will be resolved by a previous rule).

3. instantiating via callback with additional params allowed. Same callback than
   in the first rule, but with additional arguments. Use this form when specific
   objects need additional arguments to pass into constructor. You can specify
   a fixed number of arguments or use php-5.6 specific `...` operator.

4. it is also possible to just return existing objects and even scalars, as
   shown in 3 last examples.

You can have as many rule files as needed, so each module in your application
may come with its own descriptions.

### Step 2: instantiate ServiceContainer with the list of rule files

```php
<?php

// bootstrap.php

$app = new \Vertilia\Container\ServiceContainer([__DIR__ . '/services.php']);
```

You may now ask the `$app` service container to return you the object of needed
class. It will take all the necessary actions to find it in its store or create
it if necessary.

```php
$svc = $app->get(\Vertilia\Container\ServiceMock::class);

print_r($svc->env);
```

Please see more usage cases in `tests/`.

## Final note

Service Containers together with Dependency Injection techniques do a great job
to remove singletons from our applications, help decouple our code, make it more
testable and SOLID.
