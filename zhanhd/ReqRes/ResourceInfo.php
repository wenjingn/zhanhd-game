<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Player as Owner;

/**
 *
 */
class ResourceInfo extends Box
{
    /**
     *
     * @param  Owner $o
     * @return void
     */
    public function fromOwnerObject(Owner $o)
    {
        $gold    = 1;
        $weapon  = 2;
        $armor   = 3;
        $soldier = 4;
        $food    = 5;
        $wood    = 6;
        $horse   = 7;

        $this->gold   ->intval($o->profile->$gold);
        $this->weapon ->intval($o->profile->$weapon);
        $this->armor  ->intval($o->profile->$armor);
        $this->soldier->intval($o->profile->$soldier);
        $this->food   ->intval($o->profile->$food);
        $this->wood   ->intval($o->profile->$wood);
        $this->horse  ->intval($o->profile->$horse);
        $this->diamond->intval($o->gold);
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('gold',    new U32);
        $this->attach('weapon',  new U32);
        $this->attach('armor',   new U32);
        $this->attach('soldier', new U32);
        $this->attach('food',    new U32);
        $this->attach('wood',    new U32);
        $this->attach('horse',   new U32);
        $this->attach('diamond', new U32);
    }
}
