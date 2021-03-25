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
use ArrayObject,
    Exception,
    Countable,
    IteratorAggregate;

/**
 *
 */
class Object implements Countable, IteratorAggregate
{
    /**
     * @var ArrayObject
     */
    private $object = null;

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->object = new ArrayObject;
    }

    /**
     *
     * @param  mixed $k
     * @param  mixed $v
     * @return void
     */
    public function __set($k, $v)
    {
        return $this->set($k, $v);
    }

    /**
     *
     * @param  mixed $k
     * @return mixed
     */
    public function __get($k)
    {
        return $this->get($k);
    }

    /**
     *
     * @param  mixed $k
     * @return boolean
     */
    public function __isset($k)
    {
        if ($k === null) {
            return false;
        }

        return $this->object->offsetExists($k);
    }

    /**
     *
     * @param  mixed $k
     * @return void
     */
    public function __unset($k)
    {
        if ($k === null) {
            return;
        }

        if ($this->object->offsetExists($k)) {
            $this->object->offsetUnset($k);
        }
    }

    /**
     *
     * @param  mixed $method
     * @param  array $argv
     * @return mixed
     */
    public function __call($method, array $argv)
    {
        if ($method === null || false === $this->object->offsetExists($method)) {
            throw new Exception('call undefined function `'.$method.'`');
        }

        if (is_callable(($callback = $this->object->offsetGet($method)))) {
            return $callback( ... $argv);
        }
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
     * @param  mixed $k
     * @param  mixed $v
     * @return Object
     */
    public function set($k, $v)
    {
        if (is_array($v)) {
            $this->object->offsetSet($k, (new static)->import($v));
        } else {
            $this->object->offsetSet($k, $v);
        }

        return $this;
    }

    /**
     *
     * @param  mixed $k
     * @param  mixed $x
     * @return mixed
     */
    public function get($k, $x = null)
    {
        if ($k === null) {
            return;
        } else if (false === $this->object->offsetExists($k)) {
            if ($x === null) {
                return;
            }

            $this->set($k, $x);
        }

        return $this->object->offsetGet($k);
    }

    /**
     *
     * @return mixed
     */
    public function head()
    {
        if ($this->object->count() == 0) {
            return;
        }

        $it = $this->object->getIterator();
        $it->seek(0);

        return $it->current();
    }

    /**
     *
     * @return mixed
     */
    public function tail()
    {
        if (($count = $this->object->count()) == 0) {
            return;
        }

        $it = $this->object->getIterator();
        $it->seek($count - 1);

        return $it->current();
    }

    /**
     *
     * @param  callable $callback
     * @param  boolean  $reindex
     * @return Object
     */
    public function filter(callable $callback, $reindex = false)
    {
        $object = new static;
        foreach ($this->object as $k => $v) {
            if ($callback($v, $k)) {
                if ($reindex) {
                    $object->set(null, $v);
                } else {
                    $object->set($k, $v);
                }
            }
        }

        return $object;
    }

    /**
     *
     * @param callable|null $vc
     * @param callable|null $kc
     * @return Object
     */
    public function map(callable $vc = null, callable $kc = null)
    {
        if ($vc === null && $kc === null) {
            return $this;
        }

        $object = new static;
        foreach ($this->object as $k => $v) {
            $object->set(($kc ? $kc($k, $v) : $k), ($vc ? $vc($v, $k) : $v));
        }

        return $object;
    }

    /**
     *
     * @param  callable $callback
     * @param  mixed    $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        $reduced = $initial;
        foreach ($this->object as $k => $v) {
            $reduced = $callback($reduced, $v, $k);
        }

        return $reduced;
    }

    /**
     *
     * @param  boolean $reindex
     * @return Object
     */
    public function keys($reindex = false)
    {
        $object = new static;
        foreach ($this->object as $k => $v) {
            if ($reindex) {
                $object->set(null, $k);
            } else {
                $object->set($k,   $k);
            }
        }

        return $object;
    }

    /**
     * @param integer $index
     * @param integer $length
     * @param boolean $reindex
     * @return Object
     */
    public function sub($index, $length, $reindex = true)
    {
        $object = new static;
        $i = 0;
        $switch = false;
        foreach ($this->object as $k => $v) {
            if ($i == $index) {
                $switch = true;
            }

            if ($switch && $length-- > 0) {
                if ($reindex) {
                    $object->set(null, $v);
                } else {
                    $object->set($k, $v);
                }
            }
            $i++;
        }
        return $object;
    }

    /**
     *
     * @param  string $delimiter
     * @return string
     */
    public function join($delimiter)
    {
        $i = 0; $j = $this->object->count();
        $s = '';

        foreach ($this->object as $v) {
            $s .= $v;
            if (++$i <> $j) {
                $s .= $delimiter;
            }
        }

        return $s;
    }

    /**
     *
     * @param  callable $callback
     * @param  string   $method
     * @return Object
     */
    public function sort(callable $callback, $method = 'uasort')
    {
        switch ($method) {
        case 'uasort':
        case 'uksort':
            $this->object->$method($callback);
            break;
        }

        return $this;
    }

    /**
     *
     * @return Object
     */
    public function purge()
    {
        if ($this->object->count()) {
            $this->object = new ArrayObject;
        }

        return $this;
    }

    /**
     *
     * @param  array $import
     * @return Object
     */
    public function import(array $import)
    {
        foreach ($import as $k => $v) {
            $this->set($k, $v);
        }

        return $this;
    }

    /**
     *
     * @param  boolean $reindex
     * @return array
     */
    public function export($reindex = false)
    {
        $export = [];
        foreach ($this->object as $k => $v) {
            if ($v instanceof Object) {
                $v = $v->export($reindex);
            }

            if ($reindex) {
                $export[  ] = $v;
            } else {
                $export[$k] = $v;
            }
        }

        return $export;
    }

    /**
     *
     * @return integer
     */
    public function count()
    {
        return $this->object->count();
    }

    /**
     *
     * @return Traversable
     */
    public function getIterator()
    {
        return $this->object->getIterator();
    }
}
