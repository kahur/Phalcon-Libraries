<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 19/08/2017
 * Time: 02:36
 */

namespace AW\PhalconConfig;

use AW\PhalconConfig\Exceptions\ClassNotFound;
use AW\PhalconConfig\Exceptions\FileNotFound;
use AW\PhalconConfig\Exceptions\FileTypeNotSupported;
use AW\PhalconConfig\Exceptions\UnsupportedAdapter;
use AW\PhalconConfig\Inerfaces\ImporterInterface;
use AW\PhalconConfig\Inerfaces\ReaderInterface;
use Phalcon\Annotations\Reader;

class Config
{
    /**
     * @var array
     */
    protected $configAdapters;

    /**
     * @var ImporterInterface
     */
    protected $importer;

    /**
     * @var ReaderInterface
     */
    protected $reader;

    public function __construct(ImporterInterface $importer, ReaderInterface $reader)
    {
        $this->importer = $importer;
        $this->reader = $reader;
    }

    /**
     * @param string $extension
     * @param string $className
     * @throws ClassNotFound
     */
    public function addAdapter(string $extension, string $className)
    {
        if (!class_exists($className)) {
            throw new ClassNotFound('Adapter class '.$className. 'not found.');
        }

        $this->configAdapters[$extension] = $className;

    }

    /**
     * @param string $path
     * @return \Phalcon\Config|null
     */
    public function getAdapter(string $path)
    {
        $ext = explode('.', $path);
        $ext = end($ext);

        $adapterClass = $this->configAdapters[$ext] ?? null;

        if (!$adapterClass) {
            return null;
        }

        $adapter = new $adapterClass($path);

        if (!$adapter instanceof \Phalcon\Config) {
            throw new UnsupportedAdapter('Unsupported adapter');
        }

        return $adapter;
    }

    /**
     * @param string $path
     * @throws FileNotFound
     * @throws FileTypeNotSupported
     *
     * @return ReaderInterface
     */
    public function fromFile(string $path)
    {
        if (!file_exists($path)) {
            throw new FileNotFound('File '.$path. 'not found');
        }

        if (!$adapterClass = $this->getAdapter($path)) {
            throw new FileTypeNotSupported('File type does not have supported adapter');
        }

        $config = new $adapterClass($path);

        if (!empty($config->import)) {
            $this->importer->import($config, [$this, 'getAdapter']);
        }

        return $this->reader->fromConfig($config);

    }
}