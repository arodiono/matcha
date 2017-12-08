<?php

namespace App\Middleware;

/**
 * Class Middleware
 * @package App\Middleware
 */
class Middleware
{
    /**
     * @var \Slim\Container
     */
    protected $container;

    /**
     * Middleware constructor.
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     * @return object
     */
    public function __get($name): object
    {
        if ($this->container->{$name}) {
            return $this->container->{$name};
        }
    }
}