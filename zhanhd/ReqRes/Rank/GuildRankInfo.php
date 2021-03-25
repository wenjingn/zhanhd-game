<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Rank;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Str,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Guild;

/**
 *
 */
class GuildRankInfo extends Box
{
    /**
     *
     * @param  Guild  $g
     * @param  integer $rank
     * @param  integer $score
     * @return void
     */
    public function fromGuildObject(Guild $g, $rank, $score)
    {
        list($level, $power) = $g->parseRankScore($score);

        $this->name ->strval($g->name);
        $this->rank ->intval($rank);
        $this->level->intval($level);
        $this->power->intval($power);
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('name',  new Str);
        $this->attach('rank',  new U16);
        $this->attach('level', new U16);
        $this->attach('power', new U32);
    }
}
