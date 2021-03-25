<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player\Coherence;

/**
 *
 */
use PDOException;

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
     * @const integer
     */
    const FRIENDSHIP_INCR  = 10;
    const FRIENDSHIP_LIMIT = 400;
    const ROBBED_LIMIT = 20;
    
    /**
     * @var string
     */
    protected static $table = '`zhanhd.player`.`PlayerCoherenceDaily`';

    /**
     * @parma PhpPdo $pdo
     * @param integer $pid
     * @param integer $date
     * @param string $k
     * @param integer $limit
     * @return boolean
     */
    public static function increase(PhpPdo $pdo, $pid, $date, $k, $incr = 1, $limit = 0)
    {
        if ($limit) {
            $stmt = $pdo->prepare('SELECT COUNT(1) FROM '.self::$table.' WHERE `pid`=? AND `date`=? AND `k`=?');
            $stmt->execute([$pid, $date, $k]);
            if ($stmt->fetchColumn(0)) {
                return self::increaseReal($pdo, $pid, $date, $k, $incr, $limit); 
            }
            
            try {
                $stmt = $pdo->prepare('INSERT INTO '.self::$table.' VALUES (?, ?, ?, ?)');
                $stmt->execute([$pid, $date, $k, $incr]);
                return (boolean)$stmt->rowCount();
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    return self::increaseReal($pdo, $pid, $date, $k, $incr, $limit);
                }
                return false;
            }
        } else {
            /* no limit : insert into on duplicate key update */
            $stmt = $pdo->prepare('INSERT INTO '.self::$table.' VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `v`=`v`+?');
            $stmt->execute([$pid, $date, $k, $incr, $incr]);
            return (boolean)$stmt->rowCount();
        }
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $pid
     * @param integer $date
     * @param string $k
     * @param integer $incr
     * @param integer $limit
     * @return boolean
     */
    protected static function increaseReal(PhpPdo $pdo, $pid, $date, $k, $incr, $limit)
    {
        $stmt = $pdo->prepare('UPDATE '.self::$table.' SET `v`=`v`+? WHERE `pid`=? AND `date`=? AND `k`=? AND `v`<=?');
        $stmt->execute([$incr, $pid, $date, $k, $limit-$incr]);
        return (boolean)$stmt->rowCount();
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $pid
     * @param integer $date
     * @param string $k
     * @return integer
     */
    public static function get(PhpPdo $pdo, $pid, $date, $k)
    {
        $stmt = $pdo->prepare('SELECT `v` FROM '.self::$table.' WHERE `pid`=? AND `date`=? AND `k`=?');
        $stmt->execute([$pid, $date, $k]);
        return $stmt->fetchColumn(0);
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $pid
     * @param integer $date
     * @parma string $k
     * @return integer
     */
    public static function increaseFriendShip(PhpPdo $pdo, $pid, $date)
    {
        return self::increase($pdo, $pid, $date, 'friendship', 10, self::FRIENDSHIP_LIMIT);
    }
}
