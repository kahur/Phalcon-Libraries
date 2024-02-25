<?php
namespace AW\PhalconContainerBuilder\Interfaces;

use AW\PhalconConfig\Interfaces\ReaderInterface;

/**
 * @package AW
 * @author Kamil Hurajt <hurajtk@gmail.com>
 */
interface ServiceProviderInterface
{
    /**
     * @param Config $config
     * @return mixed
     */
    public function build(ReaderInterface $config);
}