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
abstract class Int implements ReqResInterface
{
    /**
     * @var integer
     */
    protected $intval = null;

    /**
     * @var string
     */
    protected $format = null;

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->reload();
    }

    /**
     *
     * @return void
     */
    public function __clone()
    {
        $this->reload();
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
     * @param  integer|null $intval
     * @return integer|ReqResInterface
     */
    public function intval($intval = null)
    {
        if ($intval === null) {
            return $this->intval;
        }

        $this->intval = $intval;
        return $this;
    }

    /**
     *
     * @param  integer $step
     * @return ReqResInterface
     */
    public function incr($step = 1)
    {
        $this->intval += $step;
        return $this;
    }

    /**
     *
     * @param  integer $step
     * @return ReqResInterface
     */
    public function decr($step = 1)
    {
        $this->intval -= $step;
        return $this;
    }

    /**
     *
     * @param  integer $bit
     * @return ReqResInterface
     */
    public function bitset($bit)
    {
        $this->intval |= $bit;
        return $this;
    }

    /**
     *
     * @param  integer $bit
     * @return ReqResInterface
     */
    public function bitrem($bit)
    {
        $this->intval &= ~$bit;
        return $this;
    }

    /**
     *
     * @param  integer $bit
     * @return boolean
     */
    public function bithas($bit)
    {
        return (boolean) ($this->intval & $bit);
    }

    /**
     *
     * @return string
     */
    public function encode()
    {
        return pack($this->format, $this->intval);
    }

    /**
     *
     * @param  string  $packed
     * @param  integer $offset
     * @return ReqResInterface
     */
    public function decode($packed, $offset = 0)
    {
        $this->intval = array_sum(unpack($this->format, substr($packed, $offset, $this->length())));
        return $this;
    }

    /**
     *
     * @return ReqResInterface
     */
    public function reload()
    {
        $this->intval = 0;
    }

    /**
     *
     * @return array
     */
    public function export()
    {
        return array(
            $this->format => $this->intval,
        );
    }
}
