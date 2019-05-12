<?php

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
