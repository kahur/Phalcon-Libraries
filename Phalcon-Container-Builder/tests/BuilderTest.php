<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 25/08/2017
 * Time: 23:20
 */

namespace AW\PhalconContainerBuilder\Test;

use AW\PhalconConfig\Config;
use AW\PhalconConfig\Importer;
use AW\PhalconConfig\Reader;
use AW\PhalconContainerBuilder\Builder;
use AW\PhalconContainerBuilder\Test\Stubs\TestClass;
use Phalcon\Config\Adapter\Yaml;
use Phalcon\Di\FactoryDefault;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    protected $config;
    public function setUp()
    {
        $resource = 'tests/resources/test.yml';

        $config = new Config(
            new Importer(),
            new Reader()
        );
        $config->addAdapter('yml', Yaml::class);

        $this->config = $config->fromFile($resource);
    }

    public function testBuild()
    {
        $di = new FactoryDefault();
        $builder = new Builder($this->config, $di);
        $builder->build();

        $builded = $di->get('myService');
        $expect = new TestClass('test', 'test1');

        $this->assertEquals($expect, $builded);

    }

    public function testBuildService()
    {
        $builder = new Builder($this->config, new FactoryDefault());
        $serviceData = [
            'class' => TestClass::class,
            'arguments' => [
                'test',
                'test1'
            ]
        ];

        $service = $builder->buildService($serviceData);
        $expect = new TestClass('test', 'test1');

        $this->assertEquals($expect, $service);
    }
}