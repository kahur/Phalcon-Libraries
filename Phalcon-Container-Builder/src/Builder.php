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
        $arguments = isset($service['arguments']) ? $service['arguments'] : [];

        $injectArgs = [];
        foreach ($arguments as $name => $argument) {
            $value = $this->getValue($argument, $name);

            if (is_array($value)) {
                $value = [$name => $value];
//                var_dump($value);
//                exit;
                $injectArgs = array_merge($injectArgs, $value);
            } else {
                $injectArgs[$name] = $value;
            }
        }

        $serviceObject = new \ReflectionClass($service['class']);

        return $serviceObject->newInstanceArgs($injectArgs);
    }

    /**
     * @param $argument
     * @param string $name
     * @return array
     */
    protected function resolveReference($argument)
    {
        $path = explode('.', substr($argument, 1));
        $result = null;
        foreach ($path as $pointer) {
            $value = $this->config->{$pointer};
        }

        if (!is_string($value) && isset($value->toArray()['class'])) {
            $serviceData = $value->toArray();
            $result = $this->buildService($serviceData);
        } else {
            $result = $value;
        }

        return $result;
    }

    /**
     * @param $argument
     * @param null $name
     * @return array|mixed|null
     */
    protected function getValue($argument, $name = null)
    {
        $value = null;
        if (is_array($argument)) {
            $value = [];
            foreach ($argument as $name => $v) {
                $value[$name] = $this->getValue($v);
            }
        } else {
            $value = (substr($argument, 0, 1) === '@') ? $this->resolveReference($argument) : $argument;
        }

        return $value;
    }


}