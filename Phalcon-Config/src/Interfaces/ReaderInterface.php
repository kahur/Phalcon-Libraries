<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 19/08/2017
 * Time: 03:04
 */

namespace AW\PhalconConfig\Interfaces;


use Phalcon\Config;

interface ReaderInterface
{
    /**
     * @param Config $config
     * @return ReaderInterface
     */
    public function fromConfig(Config $config);
}