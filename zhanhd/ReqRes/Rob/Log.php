<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Rob;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U08,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\ReqRes\PvpRank\Target as PvpRankTarget,
    Zhanhd\Object\Player;

/**
 *
 */
class Log extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('id', new U64);
        $this->attach('robber', new PvpRankTarget);
        $this->attach('time', new U32);
        $this->attach('resource', new U16);
        $this->attach('replayId', new U64);
        $this->attach('retval',   new U08);
    }

    /**
     * @param RobLog $log
     * @param Player $robber
     * @return void
     */
    public function fromObject($log, Player $robber = null)
    {
        $this->id->intval($log->id);
        if ($robber === null) {
            $robber = new Player;
            $robber->find($log->robber);
        }
        $this->robber->fromPlayerObject($robber, 0);
        $this->time->intval((int)($log->created/1000000));
        $this->resource->intval($log->getResourceTotal());
        $this->replayId->intval($log->replay);
        $this->retval->intval($log->retval);
    }
}
