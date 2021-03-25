<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player\Entity;

/**
 *
 */
use System\Stdlib\PhpPdo,
    System\Stdlib\Object,
    System\Object\DatabaseProfile;

/**
 *
 */
class Refine extends DatabaseProfile
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerEntityRefine`';
    }

    /**
     * @param integer $peid
     * @return void
     */
    public function setPlayerEntityId($peid)
    {
        $this->where->peid = $peid;
        return $this;
    }

    /**
     * @return integer
     */
    public function getPlayerEntityId()
    {
        return $this->where->peid;
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $pid
     * @return Object
     */
    public static function gets(PhpPdo $pdo, $pid)
    {
        $stmt = $pdo->prepare('SELECT per.* from `zhanhd.player`.`PlayerEntityRefine` per left join `zhanhd.player`.`PlayerEntity` pe on (per.peid = pe.id) where pid=?');
        $stmt->execute([$pid]);
        $objs = $stmt->fetchAll(PhpPdo::FETCH_OBJ);
        $ret = new Object;
        foreach ($objs as $o) {
            $peid = $o->peid;
            if (false === isset($ret->$peid)) {
                $ret->$peid = new self;
                $ret->$peid->setPlayerEntityId($peid);
            }
            $ret->$peid->{$o->k} = $o->v;
        }

        return $ret;
    }
}
