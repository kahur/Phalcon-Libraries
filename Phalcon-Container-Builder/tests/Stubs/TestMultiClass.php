<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 27/08/2017
 * Time: 17:20
 */

namespace AW\PhalconContainerBuilder\Test\Stubs;


class TestMultiClass
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }
}