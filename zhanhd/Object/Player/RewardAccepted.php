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
use System\Object\DatabaseObject,
    System\Stdlib\PhpPdo,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Object\Player;

/**
 *
 */
class RewardAccepted extends DatabaseObject
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerRewardAccepted`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'pid'  => 0,
            'prid' => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'pid'  => null,
            'prid' => null,
        ];
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $p
     * @return Object
     */
    public static function gets(PhpPdo $pdo, $pid)
    {
        $sql = 'SELECT `prid` FROM `zhanhd.player`.`PlayerRewardAccepted` WHERE `pid` = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $pid,
        ]);

        $ret = new Object;
        while ($prid = $stmt->fetchColumn(0)) {
            $ret->set($prid, 1);
        }
        return $ret;
    }
}
