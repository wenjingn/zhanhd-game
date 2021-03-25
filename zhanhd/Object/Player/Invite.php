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
    System\Stdlib\PhpPdo;

/**
 *
 */
use Zhanhd\Object\Player;

/**
 *
 */
class Invite extends DatabaseObject
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerInvite`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'pid'     => 0,
            'invitee' => 0,
            'created' => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'pid'     => null,
            'invitee' => null,
        ];
    }

    /**
     * @return void
     */
    protected function preInsert()
    {
        $this->created = $this->ustime;
    }

    /**
     * @param PhpPdo $pdo
     * @param Player $p
     * @return boolean
     */
    public static function beInvited(PhpPdo $pdo, Player $p)
    {
        $stmt = $pdo->prepare('SELECT COUNT(1) FROM `zhanhd.player`.`PlayerInvite` WHERE `invitee`=? LIMIT 1');
        $stmt->execute([
            $p->id,        
        ]);
        return (boolean)$stmt->fetchColumn(0);
    }

    /**
     * @param PhpPdo $pdo
     * @param Player $p
     * @return Object
     */
    public static function gets(PhpPdo $pdo, Player $p)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`PlayerInvite` WHERE `pid`=?', [
            $p->id,
        ], false, 'invitee');
    }
}
