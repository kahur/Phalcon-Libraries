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
use Phalcon\Config\ConfigInterface;

class Importer implements ImporterInterface
{

    /**
     * @param ConfigInterface $config
     * @param callable $adapterCallback
     * @param string $realPath
     * @return ConfigInterface
     *
     * @throws FileNotFound
     */
    public function import(ConfigInterface $config, callable $adapterCallback, string $realPath)
    {
        foreach ($config->import as $resourceConfig) {
            $source = is_string($resourceConfig) ? $resourceConfig : null;

            if ($source && is_object($resourceConfig) && empty($resourceConfig->resource)) {
                throw new \RuntimeException('Invalid configuration format');
            }

            $source = $source ?? $resourceConfig->resource;

            $source = $this->getRealConfigPath($realPath, $source);

            if (!file_exists($source)) {
                throw new FileNotFound('File: '.$source. ' not found.');
            }

            $importedConfig = call_user_func($adapterCallback, $source);

            $config->merge($importedConfig);
        }

        return $config;
    }

    protected function getRealConfigPath($sourceConfigPath, $configPath)
    {
        $directory = dirname($sourceConfigPath);

        if (substr($configPath, 0, 2) === './') {
            return $directory . DIRECTORY_SEPARATOR . substr($configPath, 2);
        }

        if (substr($configPath, 0, 1) !== '/') {
            return $directory . DIRECTORY_SEPARATOR . $configPath;
        }

        return $configPath;
    }
}