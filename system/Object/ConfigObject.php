<?php
/**
 * $Id$
 */

/**
 *
 */
namespace System\Object;

/**
 *
 */
use stdClass;

/**
 *
 */
abstract class ConfigObject
{
    /**
     * @var stdClass
     */
    protected $object;

    /**
     * @param stdClass $object
     * @return void
     */
    public function __construct(stdClass $object)
    {
        $this->object = $object;
        $this->initial();
    }

    /**
     * @return void
     */
    protected function initial(){}

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (!isset($this->object->$key)) {
            return null;
        }
        return $this->object->$key;
    }

    /**
     * @comment unwritable
     * 
     * @param string $k
     * @param mixed  $v
     * @return void
     */
    public function __set($k, $v)
    {}

    /**
     * @param  string $k
     * @return boolean
     */
    public function __isset($k)
    {
        return isset($this->object->$k);
    }

    /**
     * @comment unwritable
     *
     * @param string $k
     * @return void
     */
    public function __unset($k)
    {}
}
