<?php
/**
 *
 */
namespace Zhanhd\ReqRes\Activity;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Str,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\Object\Player;

/**
 *
 */
class RankInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('rank',  new U32);
        $this->attach('score', new U32);
        $this->attach('pid',   new U64);
        $this->attach('nick',  new Str);
    }

    /**
     * @return void
     */
    public function fromPlayerObject(Player $p, $rank, $score)
    {
        $this->rank->intval($rank);
        $this->score->intval($score);
        $this->pid->intval($p->id);
        $this->nick->strval($p->name);
    }
}
