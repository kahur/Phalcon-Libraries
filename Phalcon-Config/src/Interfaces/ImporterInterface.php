<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 19/08/2017
 * Time: 02:37
 */

namespace AW\PhalconConfig\Interfaces;

use Phalcon\Config\ConfigInterface;

interface ImporterInterface
{
    /**
     * @param ConfigInterface $config
     * @param callable $adapterCallback
     * @param string $realPath
     *
     * @return ConfigInterface
     */
    public function import(ConfigInterface $config, callable $adapterCallback, string $realPath);
}