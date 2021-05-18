<?php
declare(strict_types=1);

namespace Vertilia\Container;

use Psr\Container\ContainerInterface;

class ServiceContainer implements ContainerInterface
{
    /** @var array */
    private array $container = [];
    /** @var array */
    private array $store = [];

    /**
     * Create new container optionally calling loadFrom().
     * When an array of files is provided, latter entries overwrite existing ones.
     *
     * @param array|string $filepath path to configuration file (or a list of configuration files to load)
     */
    public function __construct($filepath = null)
    {
        if (is_array($filepath)) {
            foreach ($filepath as $file) {
                $this->loadFrom($file);
            }
        } elseif (isset($filepath)) {
            $this->loadFrom($filepath);
        }
    }

    /**
     * Load dependency injection configuration from a filename
     * @param string $filename
     */
    public function loadFrom(string $filename)
    {
        $container = include $filename;
        if (is_array($container)) {
            $this->setContainer($container);
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Config file %s must return an array with service container definition',
                $filename
            ));
        }
    }

    /**
     * @param array $container
     */
    public function setContainer(array $container)
    {
        $this->container = array_replace($this->container, $container);
    }

    /**
    * (Creates, stores and) returns the object instance.
    * Does not store created object if additional params
    * were specified in $args
    * @param string $id     class name of returned object
    * @param array $args    additional params to constructor
    * @return mixed
    */
    public function get($id, ...$args)
    {
        if (empty($this->store[$id]) or $args) {
            // instantiate depending on specs from container
            if (! isset($this->container[$id])) {
                // if undefined then instantiate with new
                $obj = new $id(...$args);
            } elseif (is_callable($this->container[$id])) {
                // the Closure will instantiate the object
                $obj = $this->container[$id](...$args);
            } elseif (is_array($this->container[$id])) {
                // array of dependencies:
                // get each dependency and pass the list
                // to class constructor
                $args_default = [];
                foreach ($this->container[$id] as $dep) {
                    $args_default[] = $this->get($dep);
                }
                $obj = new $id(...array_merge($args_default, $args));
            } else {
                // another object or value
                $obj = $this->container[$id];
            }

            if ($args) {
                // do not store result if args present
                return $obj;
            } else {
                // store result
                $this->store[$id] = $obj;
            }
        }

        // return stored result
        return $this->store[$id];
    }

    /**
    * Whether the class is configured
    * @param string $id object class name
    * @return bool
    */
    public function has($id): bool
    {
        return isset($this->container[$id]);
    }
}
