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
class Str implements ReqResInterface
{
    /**
     * @var Int
     */
    private $length = null;

    /**
     * @var string
     */
    private $strval = null;

    /**
     *
     * @param  Int|null $length
     * @return void
     */
    public function __construct(Int $length = null)
    {
        $this->length = $length ?: new Int\U16;
        $this->strval = '';
    }

    /**
     *
     * @return void
     */
    public function __clone()
    {
        $this->length = clone $this->length;
        $this->strval = '';
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
     * @param  string|null $strval
     * @return string|ReqResInterface
     */
    public function strval($strval = null)
    {
        if ($strval === null) {
            return $this->strval;
        }

        $this->strval = $strval;
        $this->length->intval(strlen($strval));

        return $this;
    }

    /**
     *
     * @return integer
     */
    public function strlen()
    {
        return $this->length->intval();
    }

    /**
     *
     * @param  integer $start
     * @param  integer $length
     * @return string
     */
    public function substr($start, $length)
    {
        return substr($this->strval, $start, $length);
    }

    /**
     *
     * @param  array $argv
     * @return ReqResInterface
     */
    public function concat( ... $argv)
    {
        foreach ($argv as $str) {
            $this->strval .= $str;
            $this->length->incr(strlen($str));
        }

        return $this;
    }

    /**
     *
     * @param  string $fmt
     * @param  array  $argv
     * @return ReqResInterface
     */
    public function strfmt($fmt, ... $argv)
    {
        $this->strval(vsprintf($fmt, $argv));
        return $this;
    }

    /**
     *
     * @param  callable $callback
     * @return ReqResInterface
     */
    public function strcbk(callable $callback)
    {
        $this->strval($callback($this->strval));
        return $this;
    }

    /**
     *
     * @return string
     */
    public function encode()
    {
        return $this->length->encode() . $this->strval;
    }

    /**
     *
     * @param  string  $packed
     * @param  integer $offset
     * @return ReqResInterface
     */
    public function decode($packed, $offset = 0)
    {
        if (($strlen = $this->length->decode($packed, $offset)->intval()) == 0) {
            return $this;
        }

        $this->strval = substr($packed, $offset + $this->length->length(), $strlen);
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function length()
    {
        return $this->length->length() + $this->length->intval();
    }

    /**
     *
     * @return ReqResInterface
     */
    public function reload()
    {
        $this->length->reload();
        $this->strval = '';

        return $this;
    }

    /**
     *
     * @return array
     */
    public function export()
    {
        return array_merge($this->length->export(), array(
            'a' => $this->strval,
        ));
    }
}
