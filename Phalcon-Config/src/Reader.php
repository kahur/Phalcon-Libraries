<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 19/08/2017
 * Time: 03:09
 */

namespace AW\PhalconConfig;
use AW\PhalconConfig\Interfaces\ReaderInterface;
use AW\PhalconConfig\Reader\Value;
use Phalcon\Config;

class Reader implements ReaderInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $cursor;

    /**
     * @param Config $config
     * @return Reader
     */
    public function fromConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param $name
     * @return string
     */
    public function getCursor(string $name = null)
    {
        if (!$name) {
            return $this->cursor;
        }

        $cursor = ($this->cursor) ? $this->cursor.'.'.$name : $name;

        return $cursor;
    }

    /**
     * @param $name
     */
    public function setCursor(string $name)
    {
        $this->cursor = $name;
    }

    /**
     * @param $name
     * @return $this|mixed
     */
    public function get($name)
    {
        return $this->__get($name);
    }

    /**
     * @param string $name
     * @return $this|mixed
     */
    public function __get(string $name)
    {
        $path = $this->getCursor($name);

        $value = $this->config->path($path);

        if (is_object($value)) {
            $cursor = $this->getCursor($name);
            $this->setCursor($cursor);

            return $this;
        }

        if (!$value && $this->getCursor()) {
            $this->cursor = null;
            return $this->get($name);
        }

        $this->cursor = null;

        $valueObject = new Value($value);

        return $valueObject->getValue([$this, 'get']);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $cursor = $this->getCursor();

        if ($cursor) {
            return $this->config->path($cursor)->toArray();
        }

        return $this->config->toArray();
    }
}