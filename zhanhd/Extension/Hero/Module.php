<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Hero;

/**
 *
 */
use System\Swoole\Client;

/**
 *
 */
use Zhanhd\Object\Player\Lineup as PlayerLineup,
    Zhanhd\Config\HeroExp,
    Zhanhd\Config\NewzoneMission,
    Zhanhd\ReqRes\HeroUpgradeResponse,
    Zhanhd\Extension\NewzoneMission\Module as NewzoneMissionModule;

/**
 *
 */
class Module
{
    /**
     * @param Client       $c
     * @param Object       $global
     * @param PlayerLineup $pl
     * @param integer      $exp
     * @param boolean      $energyConsumed
     * @return void
     */
    public static function upgradeAspect(Client $c, $global, PlayerLineup $pl, $exp, $energyConsumed)
    {
        $upgrades = [];
        $upgraded = false;
        foreach ($pl->heros as $plh) {
            if ($plh->peid == 0) continue;

            if ($energyConsumed) {
                $plh->pe->consumeEnergy($energyConsumed);

                if ($exp && $plh->pe->lvl < HeroExp::MAX_LEVEL) {
                    if ($plh->pe->addexp($exp)) {
                        $upgraded = true;
                    }
                    $plh->pe->save();
                }

                $upgrades[] = $plh->pe;
            } else {
                if ($exp && $plh->pe->lvl < HeroExp::MAX_LEVEL) {
                    if ($plh->pe->addexp($exp)) {
                        $upgraded = true;
                    }
                    $plh->pe->save();
                    $upgrades[] = $plh->pe;
                }
            }
        }

        if ($upgraded && $pl->gid == 1) {
            NewzoneMissionModule::trigger($c->local->player, $global, NewzoneMission::TYPE_LVLSUM, $pl->getLvlsum());
        }

        if ($n = count($upgrades)) {
            $r = new HeroUpgradeResponse;
            $r->heros->resize($n);
            foreach ($r->heros as $i => $h) {
                $h->fromPlayerEntityObject($upgrades[$i], $global);
            }
            $c->addReply($r);
        }
    }
}
