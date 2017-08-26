<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 25/08/2017
 * Time: 21:22
 */

namespace AW\PhalconContainerBuilder;

use AW\PhalconConfig\Config;
use AW\PhalconConfig\Reader;
use Phalcon\Di;

class Builder
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Di
     */
    protected $di;

    /**
     * Builder constructor.
     * @param Config $config
     */
    public function __construct(Reader $config, Di $di)
    {
        $this->config = $config;
        $this->di = $di;
    }

    /**
     * Build dependency injection container and adding services
     */
    public function build()
    {
        $services = $this->config->services->toArray();

        $thisObj = $this;
        foreach ($services as $name => $service) {
            $this->di->set($name, function () use ($service, $thisObj) {
                $service = $thisObj->buildService($service);
                return $service;

            });
        }

    }

    /**
     * Build service including dependencies
     * @param array $service
     * @return object
     */
    public function buildService(array $service)
    {
        $arguments = $service['arguments'];

        $injectArgs = [];
        foreach ($arguments as $argument) {
            if (substr($argument, 0, 1) === '@') {
                $path = explode('.', substr($argument, 1));

                foreach ($path as $pointer) {
                    $value = $this->config->{$pointer};
                }


                if (!is_string($value) && isset($value->toArray()['class'])) {
                    $serviceData = $value->toArray();
                    $injectArgs[] = $this->buildService($serviceData);
                } else {
                    $injectArgs[] = $value;
                }
            } else {
                $injectArgs[] = $argument;
            }
        }

        $serviceObject = new \ReflectionClass($service['class']);

        return $serviceObject->newInstanceArgs($injectArgs);
    }


}