<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 23/08/2017
 * Time: 15:58
 */

namespace AW\PhalconConfig\Tests;

use AW\PhalconConfig\Reader;
use Phalcon\Config;
use Phalcon\Config\Adapter\Yaml;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    protected $config;

    public function setUp(): void
    {
        $this->config = $this->getMockBuilder(Yaml::class)
            ->setConstructorArgs([
                'tests/resources/test.yml'
            ])
            ->onlyMethods(['merge'])
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

    public function testMerge()
    {
        $config1 = new Config([
            'test' => 'tesst1'
        ]);

        $config2 = new Config([
            'cf2test' => 'cf2test'
        ]);

        $reader = (new Reader())->fromConfig($config1);
        $reader->merge($config2);

        $data = $reader->toArray();
        $expected = [
            'test' => 'tesst1',
            'cf2test' => 'cf2test'
        ];

        $this->assertEquals($expected, $data);
    }

    public function testNonSetValue()
    {
        $config1 = new Config([
            'test' => 'tesst1'
        ]);

        $reader = (new Reader())->fromConfig($config1);

        $this->assertFalse(isset($reader->test1));
    }

    public function testIsSetValue()
    {
        $config1 = new Config([
            'test' => 'tesst1'
        ]);

        $reader = (new Reader())->fromConfig($config1);

        $this->assertTrue(isset($reader->test));
    }

    public function testNestedNonSetValue()
    {
        $config1 = new Config([
            'test' => [
                'test2'=> 'value'
            ]
        ]);

        $reader = (new Reader())->fromConfig($config1);

        $this->assertFalse(isset($reader->test->test1));
    }

    public function testNestedIsSetValue()
    {
        $config1 = new Config([
            'test' => [
                'test2'=> 'value'
            ]
        ]);

        $reader = (new Reader())->fromConfig($config1);

        $this->assertTrue(isset($reader->test->test2));
    }



}