<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension;

/**
 *
 */
use Redis;

/**
 *
 */
class Identity
{
    /**
     * @param Redis $r
     * @return integer
     */
    public static function generatePId(Redis $r)
    {
        return $r->incr('zhanhd:int:uuid-p');
    }
    
    /**
     * @param Redis $r
     * @return integer
     */
    public static function generatePeId(Redis $r)
    {
        return $r->incr('zhanhd:int:uuid-pe');
    }

    /**
     * 
     */
    private static $sequence = -1;
    private static $msec = -1;

    /**
     * @return integer
     */
    public static function generate()
    {
        $datacenter  = 1;
        $worker = 1;
        
        $msec = (int)(ustime()/1000);
        if (self::$msec != $msec) {
            self::$msec = $msec;
            self::$sequence = 0;
        }

        return self::$msec << 22 | $datacenter << 17 | $worker << 12 | self::$sequence++;
    }
}
