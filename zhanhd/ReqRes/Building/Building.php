<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Building;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Player\Building as PlayerBuilding;

/**
 *
 */
class Building extends Box
{
    /**
     *
     * @return void
     */
    public function fromPlayerBuildingObject(PlayerBuilding $pb)
    {
        $collect = $pb->collect();

        $this->bid    ->intval($pb->bid);
        $this->cltnums->intval(end($collect));

        $this->lvl    ->intval($pb->lvl);
        $this->ttfull ->intval(max(0, $pb->getFullRemainTime()));

        $this->cltmin ->intval($pb->cbp->ctmin);

        if (isset($pb->cbp->productions)) {
            $this->prdunt->intval(end($pb->cbp->productions));
        } else {
            $this->prdunt->intval(0);
        }

        $this->ttugd  ->intval(max(0, $pb->getUpgradeRemainTime()));
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('bid',     new U32);
        $this->attach('cltnums', new U32); // number of collectable resource

        $this->attach('lvl',     new U32);
        $this->attach('ttfull',  new U32); // time-to-full

        $this->attach('cltmin',  new U32); // collect min time
        $this->attach('prdunt',  new U32); // produce per time

        $this->attach('ttugd',   new U32); // time-to-upgradation-finished
    }
}
