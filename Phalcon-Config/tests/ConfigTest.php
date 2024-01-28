<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 19/08/2017
 * Time: 03:12
 */

namespace AW\PhalconConfig\Tests;

use AW\PhalconConfig\Config;
use AW\PhalconConfig\Exceptions\UnsupportedAdapter;
use AW\PhalconConfig\Interfaces\ImporterInterface;
use AW\PhalconConfig\Interfaces\ReaderInterface;
use AW\PhalconConfig\Tests\Stubs\TestClass;
use Phalcon\Config\Adapter\Json;
use Phalcon\Config\Adapter\Yaml;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var ImporterInterface
     */
    protected $importer;

    /**
     * @var ReaderInterface
     */
    protected $reader;

    public function setUp(): void
    {
        $this->importer = $this->getMockBuilder(ImporterInterface::class)->getMock();
        $this->reader = $this->getMockBuilder(ReaderInterface::class)->getMock();
    }

    public function testAddAdapter()
    {
        $config = new Config($this->importer, $this->reader);
        $config->addAdapter('json', Json::class);

        $this->assertInstanceOf(Json::class, $config->getAdapter('tests/resources/test.json'));
    }

    public function testAddWrongAdapter()
    {
        $this->expectException(UnsupportedAdapter::class);

        $config = new Config($this->importer, $this->reader);
        $config->addAdapter('json', TestClass::class);

        $config->getAdapter('tests/resources/test.json');
    }

    public function testFromFile()
    {
        $importer = $this->getMockBuilder(ImporterInterface::class)
            ->setMethods(['import'])
            ->getMock();
        $importer
            ->expects($this->once())
            ->method('import');


        $reader = $this->getMockBuilder(ReaderInterface::class)
            ->setMethods(['fromConfig', 'merge'])
            ->getMock();
        $reader
            ->expects($this->once())
            ->method('fromConfig');

        $config = new Config($importer, $reader);
        $config->addAdapter('yml', Yaml::class);

        $config->fromFile('tests/resources/test.yml');
    }

    public function testAttach()
    {
        $importer = $this->getMockBuilder(ImporterInterface::class)
            ->setMethods(['import'])
            ->getMock();
        $importer
            ->expects($this->once())
            ->method('import');


        $reader = $this->getMockBuilder(ReaderInterface::class)
            ->setMethods(['fromConfig', 'merge'])
            ->getMock();

        $reader
            ->expects($this->once())
            ->method('fromConfig');

        $reader
            ->expects($this->once())
            ->method('merge');

        $config = new Config($importer, $reader);
        $config->addAdapter('yml', Yaml::class);

        $config->fromFile('tests/resources/test.yml');

        $config->attach('tests/resources/attach.yml');

    }
}
