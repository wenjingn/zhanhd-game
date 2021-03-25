<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Entity;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Set,
    System\ReqRes\Int\U08,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Player\Entity as PlayerEntity;

/**
 *
 */
class Hero extends Box
{
    /**
     *
     * @return void
     */
    public function fromPlayerEntityObject(PlayerEntity $pe, $global)
    {
        $this->pe->peid->intval($pe->id);
        $this->pe->eid ->intval($pe->eid);

        $this->exp->intval($pe->exp);
        $this->lvl->intval($pe->lvl);

        $this->aid->intval($pe->property->aid);
        $this->ep ->intval($pe->property->getEnergy($global->ustime));

        $this->str->intval($pe->property->str);
        $this->int->intval($pe->property->int);
        $this->stm->intval($pe->property->stm);
        $this->dex->intval($pe->property->dex);

        $revSkills = $pe->e->getRevSkills();
        $this->slvls->resize(count($revSkills));
        foreach ($this->slvls as $i => $o) {
            $lvl = $pe->skill->{$revSkills[$i]};
            $o->intval($lvl ?: 1);
        }
        $this->love->intval($pe->property->love);
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('pe', new Entity);

        $this->attach('exp', new U32);
        $this->attach('aid', new U32);
        $this->attach('lvl', new U16);

        $this->attach('ep',    new U16);
        $this->attach('epmax', new U16);

        $this->attach('str', new U16);
        $this->attach('int', new U16);
        $this->attach('stm', new U16);
        $this->attach('dex', new U16);

        $this->attach('slvls', new Set(new U08));
        $this->epmax->intval(100);

        $this->attach('love', new U16);
    }
}
