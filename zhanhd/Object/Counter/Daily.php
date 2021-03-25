<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Counter;

/**
 *
 */
use System\Stdlib\PhpPdo;

/**
 *
 */
class Daily
{
    /**
     * @var string
     */
    protected static $table = '`zhanhd.player`.`CounterDaily`';

    /**
     * @param PhpPdo $pdo
     * @param integer $day
     * @param string $k
     * @param integer $incr
     * @return boolean
     */
    public static function increase(PhpPdo $pdo, $day, $k, $incr = 1)
    {
        $cmd = $pdo->prepare('INSERT INTO '.self::$table.' VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `v`=`v`+?');
        $cmd->execute([
            $day, $k, $incr, $incr,        
        ]);

        return (boolean)$cmd->rowCount();
    }
}
