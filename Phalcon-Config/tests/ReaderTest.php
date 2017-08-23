<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 23/08/2017
 * Time: 15:58
 */

namespace AW\PhalconConfig\Tests;

use AW\PhalconConfig\Reader;
use Phalcon\Config\Adapter\Yaml;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    protected $config;

    public function setUp()
    {
        $this->config = $this->getMockBuilder(Yaml::class)
            ->setConstructorArgs([
                'tests/resources/test.yml'
            ])
            ->setMethods(['merge'])
            ->getMock();
    }

    public function testFromConfig()
    {
        $reader = new Reader();

        $this->assertInstanceOf(Reader::class, $reader->fromConfig($this->config));
    }

    public function testEmptyCursor()
    {
        $reader = (new Reader())->fromConfig($this->config);

        $this->assertNull($reader->getCursor());

    }

    public function testNonEmptyCursor()
    {
        $reader = (new Reader())->fromConfig($this->config);

        $this->assertInstanceOf(Reader::class, $reader->testSub);
        $this->assertEquals('testSub', $reader->getCursor());
        $this->assertEquals('testSub.test', $reader->getCursor('test'));
    }

    public function testToArray()
    {
        $reader = (new Reader())->fromConfig($this->config);

        $reader->testSub;

        $array = $this->config->testSub->toArray();

        $this->assertEquals($array, $reader->toArray());
    }

    public function testValue()
    {
        $reader = (new Reader())->fromConfig($this->config);

        $this->assertEquals('test', $reader->test);
    }

    public function testReferenceValue()
    {
        $reader = (new Reader())->fromConfig($this->config);
        $this->assertEquals('test', $reader->testReference->test);
    }


}