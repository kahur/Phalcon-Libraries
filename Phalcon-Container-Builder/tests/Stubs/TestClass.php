<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 25/08/2017
 * Time: 23:24
 */

namespace AW\PhalconContainerBuilder\Test\Stubs;

class TestClass
{
    protected $param;
    protected $param1;

    public function __construct($param, $param1)
    {
        $this->param = $param;
        $this->param1 = $param1;
    }
}