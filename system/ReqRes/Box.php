<?php
/**
 * $Id$
 */

/**
 *
 */
namespace System\ReqRes;

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
abstract class Box implements ReqResInterface
{
    /**
     * @var Object
     */
    private $object = null;

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->object = new Object;
        $this->initial();
    }

    /**
     *
     * @return void
     */
    public function __clone()
    {
        $this->object = new Object;
        $this->initial();
    }

    /**
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->export();
    }

    /**
     *
     * @param  string $name
     * @return ReqResInterface
     */
    public function __get($name)
    {
        return $this->object->get($name);
    }

    /**
     *
     * @return string
     */
    public function encode()
    {
        return $this->finalize()->object->reduce(function($s, ReqResInterface $o) {
            return $s . $o->encode();
        }, '');
    }

    /**
     *
     * @param  string  $packed
     * @param  integer $offset
     * @return ReqResInterface
     */
    public function decode($packed, $offset = 0)
    {
        $this->object->reduce(function($x, ReqResInterface $o) use ($packed) {
            return $x + $o->decode($packed, $x)->length();
        }, $offset);

        return $this;
    }

    /**
     *
     * @return integer
     */
    public function length()
    {
        return $this->object->reduce(function($l, ReqResInterface $o) {
            return $l + $o->length();
        }, 0);
    }

    /**
     *
     * @return ReqResInterface
     */
    public function reload()
    {
        $this->object->reduce(function($null, ReqResInterface $o) {
            $o->reload();
        });

        return $this;
    }

    /**
     *
     * @return array
     */
    public function export()
    {
        return $this->object->reduce(function($export, ReqResInterface $o, $k) {
            return array_merge($export, [
                $k => $o->export(),
            ]);
        }, []);
    }

    /**
     *
     * @param  string          $name
     * @param  ReqResInterface $object
     * @return void
     */
    protected function attach($name, ReqResInterface $object)
    {
        $this->object->set($name, $object);
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {}

    /**
     *
     * @return ReqResInterface
     */
    protected function finalize()
    {
        return $this;
    }
}
