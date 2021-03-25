<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\NewzoneMission;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Player\NewzoneMission as PlayerNewzoneMission,
    Zhanhd\Config\NewzoneMission;

/**
 *
 */
class Info extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('day', new U16);
        $this->attach('idx', new U16);
        $this->attach('state', new U32);
        $this->attach('curr',  new U32);
        $this->attach('total', new U32);
    }

    /**
     * @param NewzoneMission $m
     * @param PlayerObject $p
     * @param mixed        $pm
     */
    public function fromObject($m, $p, $pm = null)
    {
        $this->day->intval($m->getDay());
        $this->idx->intval($m->getIdx());
        $this->total->intval($m->intval);
        
        if ($pm === null) {
            $pm = new PlayerNewzoneMission;
            $pm->find($p->id, $m->id);
        }
        $this->state->intval($pm->flag);

        switch ($m->type) {
        case NewzoneMission::TYPE_DEPOSIT:
            $this->curr->intval($p->deposit > $m->intval ? $m->intval : $p->deposit);
            break;
        case NewzoneMission::TYPE_LOGIN:
            break;
        case NewzoneMission::TYPE_LVLSUM:
        case NewzoneMission::TYPE_TASK:
        case NewzoneMission::TYPE_PVPRANK:
            $this->total->intval(0);
            break;
        case NewzoneMission::TYPE_RECRUITEQUIP:
            $this->curr->intval($p->counter->equipRecruit);
            break;
        case NewzoneMission::TYPE_RECRUITHERO:
            $this->curr->intval($p->counter->resourceRecruit);
            break;
        case NewzoneMission::TYPE_HEROGAIN:
            if ($m->extra) {
                $key = sprintf('hero%dstarGain', $m->extra);
            } else {
                $key = 'heroGain';
            }
            $this->curr->intval($p->counter->$key);
            break;
        }
    }
}
