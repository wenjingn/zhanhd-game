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
class U32 extends Int
{
    /**
     * @var string
     */
    protected $format = 'V';

    /**
     *
     * @return integer
     */
    public function length()
    {
        return 4;
    }
}
