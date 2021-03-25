<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player\Relation;

/**
 *
 */
use System\Object\DatabaseObject,
    System\Stdlib\PhpPdo;

/**
 *
 */
class Love extends DatabaseObject
{
    /**
     * @const integer
     */
    const FLAG_ACCEPTING = 1;
    const FLAG_RECEIVED  = 2;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerRelationLove`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'prid' => 0,
            'gear' => 0,
            'eid'  => 0,
            'flag' => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'prid' => null,
            'gear' => null,
        ];
    }

    /**
     * @param PhpPdo $pdo
     * @param PlayerRelation $pr
     * @return Object
     */
    public static function gets(PhpPdo $pdo, $pr)
    {
        return self::buildSet(
            $pdo, 
            __CLASS__, 
            'SELECT * FROM `zhanhd.player`.`PlayerRelationLove` WHERE `prid`=?',
            [ $pr->id ],
            false,
            'gear'
        );
    }
}
