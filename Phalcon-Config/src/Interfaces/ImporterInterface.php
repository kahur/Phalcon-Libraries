<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 19/08/2017
 * Time: 02:37
 */

namespace AW\PhalconConfig\Interfaces;

use Phalcon\Config;

interface ImporterInterface
{
    /**
     * Import list of resourcesz
     *
     * @param Config $config
     * @return Config
     */
    public function import(Config $config, \Closure $adapterCallback);
}