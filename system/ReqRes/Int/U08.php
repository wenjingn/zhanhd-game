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
class U08 extends Int
{
    /**
     * @var string
     */
    protected $format = 'C';

    /**
     *
     * @return integer
     */
    public function length()
    {
        return 1;
    }
}
