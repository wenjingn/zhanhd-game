<?php
/**
 * $Id$
 */

 /**
  *
  */
namespace System\Stdlib;

/**
 *
 */
trait SingletonTrait
{
    /**
     * @var object
     */
    private static $object = null;

    /**
     *
     * @return void
     */
    private function __construct()
    {
        $this->initial();
    }

    /**
     *
     * @return void
     */
    private function __clone()
    {}

    /**
     *
     * @return void
     */
    protected function initial()
    {}

    /**
     *
     * @return object
     */
    public static function getInstance()
    {
        if (static::$object === null) {
            static::$object = new static;
        }

        return static::$object;
    }
}
