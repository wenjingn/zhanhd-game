<?php
/**
 * $Id$
 */

/**
 *
 */
namespace System\ReqRes\Int;

/**
 *
 */
use System\ReqRes\Int;

/**
 *
 */
class U64 extends Int
{
    /**
     * @var string
     */
    protected $format = 'P';

    /**
     *
     * @return integer
     */
    public function length()
    {
        return 8;
    }
}
