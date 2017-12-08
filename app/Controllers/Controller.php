<?php

namespace App\Controllers;

/**
 * Class Controller
 * @package App\Controllers
 */
class Controller
{
    /**
     * @var
     */
    protected $container;

    /**
     * Controller constructor.
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($this->container->{$name}) {
            return $this->container->{$name};
        }
    }
}