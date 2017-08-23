<?php
/**
 * Created by PhpStorm.
 * User: kamilhurajt
 * Date: 21/08/2017
 * Time: 13:50
 */

namespace AW\PhalconConfig\Reader;


class Value
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $isReference = false;

    /**
     * @var bool
     */
    protected $isEnvVariable = false;

    /**
     * Value constructor.
     * @param $value
     */
    public function __construct(string $value)
    {
        $this->initValue($value);
    }

    /**
     * @param $value
     */
    public function initValue($value)
    {
        $pointer = substr($value, 0, 1);

        switch ($pointer) {
            case '@':
                $this->isReference = true;
            break;
            case '~':
                $this->isEnvVariable = true;
            break;
            default:
                $this->isReference = false;
                $this->isEnvVariable = false;
            break;
        }

        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function isReference()
    {
        return $this->isReference;
    }

    /**
     * @return bool
     */
    public function isEnvVariable()
    {
        return $this->isEnvVariable;
    }

    /**
     * @return string
     */
    public function getValue(callable $pathFinder = null)
    {
        if ($this->isReference() && $pathFinder) {
            return call_user_func($pathFinder, substr($this->value, 1));
        } else if($this->isEnvVariable()) {
            return getenv(substr($this->value, 1));
        }

        return $this->value;
    }
}