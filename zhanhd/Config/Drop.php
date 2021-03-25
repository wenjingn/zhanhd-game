<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config;

/**
 *
 */
use System\Object\ConfigObject,
    System\Stdlib\Object;

/**
 *
 */
class Drop extends ConfigObject
{
    /**
     * @struct self
     *
     * @items array( (integer)index => (object) DropSource )
     */

    /**
     * @struct DropSource
     *
     * @type integer
     * @prob integer
     * @source array( (integer)eid|gid => (integer)prob|num )
     */

    /**
     * @const integer
     */
    const TYPE_RESOURCE = 1;
    const TYPE_RANDOM   = 2;

    /**
     * @return Object
     */
    public function drop()
    {
        $drop = new Object;

        foreach ($this->items as $item) {
            if (mt_rand(1, 100) > $item->prob) {
                continue;
            }

            switch ($item->type) {
            case self::TYPE_RESOURCE:
                foreach ($item->source as $eid => $num) {
                    $drop->$eid += $num;
                }
                break;
            case self::TYPE_RANDOM:
                $rand = mt_rand(1, array_sum($item->source));
                foreach ($item->source as $gid => $prob) {
                    if ($rand <= $prob) {
                        $eid = Store::get('egroup', $gid)->pickone();
                        $drop->$eid += 1;
                        break;
                    }

                    $rand -= $prob;
                }
                break;
            }
        }

        return $drop;
    }
}
