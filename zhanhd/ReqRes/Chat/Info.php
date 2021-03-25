<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Chat;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64,
    System\ReqRes\Str;

/**
 *
 */
use Zhanhd\ReqRes\LeaderInfo,
    Zhanhd\Object\Player;

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
        $this->attach('channel', new U16);
        $this->attach('leader',  new LeaderInfo);
        $this->attach('from',    new U64);
        $this->attach('power',   new U32);
        $this->attach('time',    new U32);
        $this->attach('content', new Str);
    }

    /**
     * @param Player $p
     * @param Request $req
     * @param Object $globals
     * @return void
     */
    public function fromPlayerObject(Player $p, Request $req, $globals)
    {
        $this->channel->intval($req->channel->intval());
        $this->leader->fromPlayerObject($p);
        $this->from->intval($p->id);
        $lineup = $p->getLineup(1);
        $this->power->intval($lineup->power);
        $this->time->intval($globals->ustime/1000000);
        $this->content->strval($req->content->strval());
    }
}
