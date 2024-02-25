<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 25/08/2017
 * Time: 21:22
 */

namespace AW\PhalconContainerBuilder;

use AW\PhalconConfig\Interfaces\ReaderInterface;
use AW\PhalconContainerBuilder\Interfaces\ServiceProviderInterface;
use Phalcon\Di;
use Phalcon\Events\Manager;

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
    protected $services;

    /**
     * @var Di
     */
    protected $di;

    /**
     * @var ReaderInterface
     */
    protected $config;

    /**
     * Builder constructor.
     * @param ReaderInterface $services
     */
    public function __construct(ReaderInterface $services, Di\DiInterface $di)
    {
        $this->services = $services;
        $this->di = $di;
        $this->config = $di->getShared('config');
    }

    /**
     * Build dependency injection container and adding services
     */
    public function build()
    {
        $services = $this->services->toArray();
        $thisObj = $this;

        $this->resolvePreInitializeServices($services);

        foreach ($services as $name => $service) {
            $this->debug = $service['debug'] ?? false;

            // debug mode for building service
            if ($this->debug) {
                $this->debugParams['service'] = $service;
                $this->debugMode($service);
                return;
            }

            if (isset($service['provider'])) {
                $this->resolveProviderService($name, $service);
                continue;
            }

            $this->di->set($name, function () use ($service, $thisObj) {
                $serviceObj = $thisObj->buildService($service);

                return $serviceObj;
            }, (isset($service['shared']) && $service['shared']) ? true : false);
        }
    }

    /**
     * @param array $services
     * @return void
     */
    protected function resolvePreInitializeServices(array &$services)
    {
        foreach ($services as $name => $service) {
            if (isset($service['preInitialize']) && $service['preInitialize']) {
                $serviceObj = $this->buildService($service);

                $this->di->set($name, $serviceObj, (isset($service['shared']) && $service['shared']) ? true : false);

                unset($services[$name]);
            }
        }
    }

    /**
     * @param string $name
     * @param array $service
     * @return void
     * @throws \Exception
     */
    protected function resolveProviderService(string $name, array $service)
    {
        $provider = new $service['provider'];

        if ($provider instanceof ServiceProviderInterface) {
            $config = $this->di->getConfig();
            $this->di->set($name, function() use($config, $provider) {
                return $provider->build($config);
            }, (isset($service['shared']) && $service['shared'] == true) ? true : false);

            return;
        }

        if ($provider instanceof Di\ServiceProviderInterface) {
            $provider->register($this->di);
            return;
        }

        throw new \Exception('Unknown provider');
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

        $reflection = new \ReflectionClass($service['className']);
        
        $serviceObject = $reflection->newInstanceArgs($injectArgs);

        if (isset($service['calls'])) {
            $this->serviceInitCalls($serviceObject, $service['calls']);
        }

        return $serviceObject;
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
        $cursor = substr($argument, 1);
        $this->services->setCursor($cursor);
        $path = explode('.', substr($argument, 1));
        $result = null;

        // try to get service
        $value = $this->services->getValue($cursor) ?? $this->config->getValue($cursor);

        if (!$value) {
            throw new \Exception("Service or param not found");
        }

        if (is_object($value) && isset($value->toArray()['className'])) {
            $serviceData = $value->toArray();
            return $this->buildService($serviceData);
        }

        return is_object($value) ? $value->toArray() : $value;
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
