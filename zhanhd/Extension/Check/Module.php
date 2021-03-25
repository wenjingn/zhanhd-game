<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Check;

/**
 *
 */
use System\Swoole\Client;

/**
 *
 */
class Module
{
    /**
     * @const integer
     */
    const CHECK_PACKAGE_ALL   = -1;
    const CHECK_PACKAGE_EQUIP = 1;
    const CHECK_PACKAGE_HERO  = 2;

    /**
     * @param Client $c
     * @param Object $global
     * @param integer $flag
     * @return boolean
     */
    public static function packageAspect(Client $c, $global, $flag = -1)
    {
        $freeCapacity = $c->local->player->freePackageCapacity();
        /**
        if (($flag & self::CHECK_PACKAGE_EQUIP) && $freeCapacity['equip'] <= 0) {
            $c->addReply($global->errorResponse->error('equip package capacity limit'));
            return false;
        }
        */

        if (($flag & self::CHECK_PACKAGE_HERO) && $freeCapacity['hero'] <= 0) {
            $c->addReply($global->errorResponse->error('hero package capacity limit'));
            return false;
        }

        return true;
    }

    /**
     * @param Client $c
     * @param Global $g
     * @param PlayerLineup $pl
     * @param integer $energy
     * @return boolean
     */
    public static function lineupAspect(Client $c, $g, $pl, $energy = 0)
    {
        if (null === $pl) {
            $c->addReply($g->errorResponse->error('invalid lineup'));
            return false;
        }
        if (false === $pl->getCaptain()) {
            $c->addReply($g->errorResponse->error('empty captain'));
            return false;
        }

        if ($energy) {
            foreach ($pl->heros as $plh) {
                if ($plh->peid == 0) continue;
                if ($plh->pe->property->getEnergy() < $energy) {
                    $c->addReply($g->errorResponse->error('notenough energy'));
                    return false;
                }
            }
        }
        return true;
    }
}
