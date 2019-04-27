<?php

use Vertilia\Container\EnvMockInterface;
use Vertilia\Container\EnvMock;
use Vertilia\Container\ServiceMock;

return [
    // instantiating via callback
    EnvMockInterface::class => function () {
        return new EnvMock();
    },

    // ServiceMock and EnvMockInterface are defined in tests/ServiceContainerTest.php
    ServiceMock::class => [EnvMockInterface::class],

    // instantiating via callback with additional params allowed
    'ServiceMockAlias' => function (...$args) {
        return new ServiceMock($this->get(EnvMockInterface::class), ...$args);
    },

    // object value
    'ServiceMockObject' => (object)'test string',

    // string value
    'ServiceMockString' => 'test string',

    // int value
    'ServiceMockInt' => 42,
];
