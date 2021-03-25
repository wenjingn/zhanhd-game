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
use IteratorAggregate;

/**
 *
 */
class Set implements ReqResInterface, IteratorAggregate
{
    /**
     * @var Object
     */
    private $object = null;

    /**
     * @var Int
     */
    private $length = null;

    /**
     * @var ReqResInterface
     */
    private $source = null;

    /**
     *
     * @param  ReqResInterface $source
     * @param  Int|null        $length
     * @return void
     */
    public function __construct(ReqResInterface $source, Int $length = null)
    {
        $this->object = new Object;
        $this->length = $length ?: new Int\U16;
        $this->source = $source;
    }

    /**
     *
     * @return void
     */
    public function __clone()
    {
        $this->object = new Object;
        $this->length = clone $this->length;
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
     * @param  integer $idx
     * @return ReqResInterface
     */
    public function __get($idx)
    {
        return $this->get($idx);
    }

    /**
     *
     * @param  integer $idx
     * @return ReqResInterface
     */
    public function get($idx)
    {
        return $this->object->get($idx);
    }

    /**
     *
     * @param  integer $size
     * @return ReqResInterface
     */
    public function resize($size)
    {
        $this->object->purge();
        $this->length->intval($size);

        for ($i = 0; $i < $size; $i++) {
            $this->object->$i = clone $this->source;
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function size()
    {
        return $this->length->intval();
    }

    /**
     * @param integer $size
     * @return Set
     */
    public function append($size)
    {
        $oldsize = $this->length->intval();
        $newsize = $oldsize + $size;
        for ($i = $oldsize; $i < $newsize; $i++) {
            $this->object->$i = clone $this->source;
        }
        $this->length->intval($newsize);
        return $this;
    }

    /**
     * @param integer $index
     * @param integer $length
     * @return void
     */
    public function sub($index, $length)
    {
        $this->object = $this->object->sub($index, $length);
        $this->length->intval($this->object->count());
    }

    /**
     *
     * @return string
     */
    public function encode()
    {
        return $this->object->reduce(function($p, ReqResInterface $o) {
            return $p . $o->encode();
        }, $this->length->encode());
    }

    /**
     *
     * @param  string  $packed
     * @param  integer $offset
     * @return ReqResInterface
     */
    public function decode($packed, $offset = 0)
    {
        if (($j = $this->length->decode($packed, $offset)->intval()) == 0) {
            return $this;
        }

        for ($i = 0, $offset += $this->length->length(); $i < $j; $offset += $this->object->get($i++)->length()) {
            $this->object->$i = clone $this->source;
            $this->object->$i->decode($packed, $offset);
        }

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
        }, $this->length->length());
    }

    /**
     *
     * @return ReqResInterface
     */
    public function reload()
    {
        $this->resize(0);
        return $this;
    }

    /**
     *
     * @return array
     */
    public function export()
    {
        return array_merge($this->length->export(), [
            's' => $this->object->reduce(function($export, ReqResInterface $o) {
                return array_merge($export, [
                    $o->export(),
                ]);
            }, []),
        ]);
    }

    /**
     *
     * @return Traveresable
     */
    public function getIterator()
    {
        return $this->object->getIterator();
    }
}
