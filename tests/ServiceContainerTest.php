<?php
declare(strict_types=1);

namespace Vertilia\Container;

use PHPUnit\Framework\TestCase;

class ServiceMock
{
    public $env;
    public $arg;

    public function __construct(EnvMock $env, $arg = null)
    {
        $this->env = $env;
        $this->arg = $arg;
    }
}

interface EnvMockInterface
{
    public function getName(): string;
    public function getRootDir(): string;
}

class EnvMock implements EnvMockInterface
{
    public function getName(): string
    {
        return self::class;
    }
    public function getRootDir(): string
    {
        return \dirname(__DIR__);
    }
}

/**
 * @coversDefaultClass \Vertilia\Container\ServiceContainer
 */
class ServiceContainerTest extends TestCase
{
    /**
     * @covers ::__construct()
     * @covers ::get()
     */
    public function testServiceContainerUndefinedClass()
    {
        $app = new ServiceContainer(__DIR__ . '/services.php');
        $this->assertInstanceOf(EnvMockInterface::class, $app->get(EnvMockInterface::class));
    }

    /**
     * @covers ::__construct()
     * @covers ::get()
     */
    public function testServiceContainerMultipleFiles()
    {
        $app = new ServiceContainer([__DIR__ . '/services.php', __DIR__ . '/services.php']);
        $this->assertInstanceOf(EnvMock::class, $app->get(EnvMock::class));
    }

    /**
     * @covers ::__construct()
     * @covers ::get()
     * @covers ::loadFrom()
     */
    public function testServiceContainerLoadFrom()
    {
        $app = new ServiceContainer();
        $app->loadFrom($app->get(EnvMock::class)->getRootDir().'/tests/services.php');
        $this->assertInstanceOf(ServiceMock::class, $app->get(ServiceMock::class));
    }

    /**
     * @covers ::__construct()
     * @covers ::get()
     * @covers ::setContainer()
     */
    public function testServiceContainerRebindClosures()
    {
        $app = new ServiceContainer();
        $container = include __DIR__ . '/services.php';
        // after inclusion $container closures must be rebound to $app
        foreach ($container as $k => &$i) {
            if ($i instanceof \Closure) {
                $i = $i->bindTo($app);
            }
        }

        $app->setContainer($container);

        /** @var ServiceMock $s */
        $s = $app->get('ServiceMockAlias');
        $this->assertInstanceOf(ServiceMock::class, $s);
        $this->assertInstanceOf(EnvMockInterface::class, $s->env);

        /** @var ServiceMock $s2 */
        $s2 = $app->get('ServiceMockAlias', 42);
        $this->assertInstanceOf(ServiceMock::class, $s2);
        $this->assertInstanceOf(EnvMockInterface::class, $s2->env);
        $this->assertEquals(42, $s2->arg);

        $this->assertInstanceOf(\stdClass::class, $app->get('ServiceMockObject'));
        $this->assertEquals('test string', $app->get('ServiceMockString'));
        $this->assertEquals(42, $app->get('ServiceMockInt'));
    }
}
