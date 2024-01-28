<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 19/08/2017
 * Time: 03:04
 */

namespace AW\PhalconConfig\Interfaces;


use Phalcon\Config\ConfigInterface;

interface ReaderInterface
{
    /**
     * @param ConfigInterface $config
     * @return ReaderInterface
     */
    public function fromConfig(ConfigInterface $config);

    /**
     * @param ConfigInterface $config
     * @return void
     */
    public function merge(ConfigInterface $config);
}