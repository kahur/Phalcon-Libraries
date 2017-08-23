<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 19/08/2017
 * Time: 03:08
 */

namespace AW\PhalconConfig;

use AW\PhalconConfig\Exceptions\FileNotFound;
use AW\PhalconConfig\Interfaces\ImporterInterface;
use Phalcon\Config;

class Importer implements ImporterInterface
{

    /**
     * Import list of resourcesz
     *
     * @param Config $config
     * @return Config
     */
    public function import(Config $config, \Closure $adapterCallback)
    {
        foreach ($config->import as $resourceConfig) {
            if (!file_exists($resourceConfig->resource)) {
                throw new FileNotFound('File: '.$resourceConfig->resource. 'not found.');
            }

            $importedConfig = call_user_func($adapterCallback, $resourceConfig->resource);

            $config->merge($importedConfig);
        }

        return $config;
    }
}