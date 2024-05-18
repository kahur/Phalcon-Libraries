<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 22/08/2017
 * Time: 12:09
 */

namespace AW\PhalconConfig\Tests;

use AW\PhalconConfig\Importer;
use Phalcon\Config;
use PHPUnit\Framework\TestCase;

class ImporterTest extends TestCase
{
    public function testImport()
    {
        $importer = new Importer();

        $adapter = $this->getMockBuilder(Config\Adapter\Yaml::class)
            ->setConstructorArgs([
                (__DIR__) . '/resources/test.yml'
            ])
            ->onlyMethods(['merge'])
            ->getMock();

        $adapter->expects($this->once())
            ->method('merge');

        $config = $this->getMockBuilder(\AW\PhalconConfig\Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAdapter'])
            ->getMock();

        $config->expects($this->once())
            ->method('getAdapter')
            ->willReturn($adapter);

        $importer->import($adapter, function($arguments) use($config){
            return $config->getAdapter($arguments);
        }, './path');
    }
}