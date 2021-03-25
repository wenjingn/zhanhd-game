<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player;

/**
 *
 */
use System\Stdlib\PhpPdo;

/**
 *
 */
class Coherence
{
    /**
     * @const integer
     */
    const INVITE_LIMIT = 10;

    /**
     * @var string
     */
    protected static $table = '`zhanhd.player`.`PlayerCoherence`';
    
    /**
     * @param PhpPdo  $pdo
     * @param integer $pid
     * @param string  $k
     * @param integer $incr
     * @param integer $limit
     * @return boolean
     */
    public static function increase(PhpPdo $pdo, $pid, $k, $incr = 1, $limit = 0)
    {
        if ($limit) {
            $stmt = $pdo->prepare('SELECT COUNT(1) FROM '.self::$table.' WHERE `pid`=? AND `k`=?');
            $stmt->execute([$pid, $k]);
            if ($stmt->fetchColumn(0)) {
                return self::increaseReal($pdo, $pid, $k, $incr, $limit);
            }
            try {
                $stmt = $pdo->prepare('INSERT INTO '.self::$table.' VALUES (?, ?, ?)');
                $stmt->execute([$pid, $k, $incr]);
                return (boolean)$stmt->rowCount();
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    return self::increaseReal($pdo, $pid, $k, $incr, $limit);
                }
                return false;
            }
        } else {
            $stmt = $pdo->prepare('INSERT INTO '.self::$table.' VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `v`=`v`+?');
            $stmt->execute([$pid, $k, $incr, $incr]);
            return (boolean)$stmt->rowCount();
        }
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $pid
     * @param string $k
     * @param integer $incr
     * @param integer $limit
     * @return boolean
     */
    protected static function increaseReal(PhpPdo $pdo, $pid, $k, $incr, $limit)
    {
        $stmt = $pdo->prepare('UPDATE '.self::$table.' SET `v`=`v`+? WHERE `pid`=? AND `k`=? AND `v`<=?');
        $stmt->execute([
            $incr, $pid, $k, $limit-$incr,
        ]);
        
        return (boolean)$stmt->rowCount();
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $pid
     * @param string $k
     * @return integer
     */
    public static function get(PhpPdo $pdo, $pid, $k)
    {
        $stmt = $pdo->prepare('SELECT `v` FROM '.self::$table.' WHERE `pid`=? AND `k`=?');
        $stmt->execute([$pid, $k]);
        return $stmt->fetchColumn(0);
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $pid
     * @param string $k
     * @param integer $decr
     * @return boolean
     */
    public static function decrease(PhpPdo $pdo, $pid, $k, $decr)
    {
        if ($decr <= 0) {
            return false;
        }
        $stmt = $pdo->prepare('UPDATE '.self::$table.' SET `v`=`v`-? WHERE `pid`=? AND `k`=? AND `v`>=?');
        $stmt->execute([$decr, $pid, $k, $decr]);
        return (boolean)$stmt->rowCount();
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $pid
     * @return boolean
     */
    public static function increaseFriendShip(PhpPdo $pdo, $pid)
    {
        return self::increase($pdo, $pid, 'friendship', 10);
    }
}
