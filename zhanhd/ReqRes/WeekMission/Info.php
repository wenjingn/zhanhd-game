<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\WeekMission;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Player\WeekMission as PlayerWeekMission,
    Zhanhd\Config\WeekMission;

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
        $this->attach('id', new U16);
        $this->attach('state', new U32);
        $this->attach('curr', new U32);
        $this->attach('total', new U32);
    }

    /**
     * @param WeekMission $m
     * @param Player $p
     * @param Object $g
     * @param mixed $pm
     * @return void
     */
    public function fromObject($m, $p, $g, $pm = null)
    {
        $this->id->intval($m->id);
        $this->total->intval($m->intval);

        if ($pm === null) {
            $pm = new PlayerWeekMission;
            $pm->find($p->id, $g->week, $m->id);
        }
        $this->state->intval($pm->flag);

        switch ($m->type) {
        case WeekMission::TYPE_DEPOSIT:
            $this->curr->intval($p->counterWeekly->diamondDeposit/10);
            break;
        case WeekMission::TYPE_LOGIN:
            $this->curr->intval($p->counterWeekly->loginDays);
            break;
        case WeekMission::TYPE_CONSUME:
            $this->curr->intval($p->counterWeekly->diamondConsume);
            break;
        case WeekMission::TYPE_HARDINS:
            $this->curr->intval($p->counterWeekly->hardins);
            break;
        case WeekMission::TYPE_CRAZYINS:
            $this->curr->intval($p->counterWeekly->crazyins);
            break;
        case WeekMission::TYPE_PVPWIN:
            $this->curr->intval($p->counterWeekly->pvpwin);
            break;
        case WeekMission::TYPE_LIKE:
            $this->curr->intval($p->counterWeekly->like);
            break;
        case WeekMission::TYPE_DIAREC:
            $this->curr->intval($p->counterWeekly->diarec);
            break;
        case WeekMission::TYPE_REFINE:
            $this->curr->intval($p->counterWeekly->refine);
            break;
        case WeekMission::TYPE_FORGE:
            $this->curr->intval($p->counterWeekly->forge);
            break;
        case WeekMission::TYPE_CRUSADE:
            $this->curr->intval($p->counterWeekly->crusade);
            break;
        case WeekMission::TYPE_TALSHOW:
            $this->curr->intval($p->counterWeekly->talent);
            break;
        }
    }
}
