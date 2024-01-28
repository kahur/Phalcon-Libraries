<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 25/08/2017
 * Time: 21:22
 */

namespace AW\PhalconContainerBuilder;

use AW\PhalconConfig\Config;
use AW\PhalconConfig\Interfaces\ReaderInterface;
use AW\PhalconConfig\Reader;
use Phalcon\Di;

class Builder
{
    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var array
     */
    protected $debugParams = [];

    /**
     * @var ReaderInterface
     */
    protected $config;

    /**
     * @var Di
     */
    protected $di;

    /**
     * Builder constructor.
     * @param ReaderInterface $config
     */
    public function __construct(ReaderInterface $config, Di $di)
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
            $this->debug = $service['debug'] ?? false;

            // debug mode for building service
            if ($this->debug) {
                $this->debugParams['service'] = $service;
                $this->debugMode($service);
                return;
            }

            $this->di->set($name, function () use ($service, $thisObj) {
                $serviceObj = $thisObj->buildService($service);
                if (isset($service['calls'])) {
                    $thisObj->serviceInitCalls($serviceObj, $service['calls']);
                }

                return $serviceObj;
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
                $injectArgs = array_merge($injectArgs, $value);
            } else {
                $injectArgs[$name] = $value;
            }
        }

        if ($this->debug) {
            $this->debugParams['construct_arguments'] = $injectArgs;
        }

        $serviceObject = new \ReflectionClass($service['class']);

        return $serviceObject->newInstanceArgs($injectArgs);
    }

    /**
     * @param $serviceObject
     * @param array $calls
     */
    public function serviceInitCalls($serviceObject, array $calls)
    {
        if ($this->debug) {
            $this->debugParams['serviceCalls'] = [];
        }

        foreach($calls as $call) {
            $method = $call['method'] ?? null;
            $arguments = $call['arguments'] ?? [];

            if (!$method) {
                continue;
            }

            if ($this->debug) {
                $this->debugParams['serviceCalls'][$method] = [];
            }

            $injectArgs = [];
            foreach ($arguments as $name => $argument) {
                $value = $this->getValue($argument, $name);

                if (is_array($value)) {
                    $value = [$name => $value];
                    $injectArgs = array_merge($injectArgs, $value);
                } else {
                    $injectArgs[$name] = $value;
                }
            }

            if ($this->debug) {
                $this->debugParams['serviceCalls'][$method] = $injectArgs;
            }

            call_user_func_array([$serviceObject, $method], $injectArgs);
        }
    }

    /**
     * Will build service upfront and display all values used to build the service
     * @param array $service
     */
    protected function debugMode(array $service)
    {
        try {
            $obj = $this->buildService($service);
            if (isset($service['calls'])) {
                $this->serviceInitCalls($obj, $service['calls']);
            }
        } catch (\Exception $e) {

        }

        echo "<pre>";
            print_r($this->debugParams);
        echo "</pre>";
        $this->debugParams = [];
        exit;
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

        if (is_object($value) && isset($value->toArray()['class'])) {
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

        if ($value instanceof ReaderInterface) {
            return $value->toArray();
        }

        if (is_numeric($value)) {
            return $value;
        }

        if (is_string($value)) {
            return (string) $value;
        }

        if (is_object($value) || is_array($value)) {
            return $value;
        }

        return $value->toArray();
    }


}
