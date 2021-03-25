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
class U16 extends Int
{
    /**
     * @var string
     */
    protected $format = 'v';

    /**
     *
     * @return integer
     */
    public function length()
    {
        return 2;
    }
}
