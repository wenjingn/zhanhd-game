<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\ReqRes\LeaderInfo;

/**
 *
 */
class MemberInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('pid',       new U64);
        $this->attach('post',      new U16);
        $this->attach('lvlsum',    new U32);
        $this->attach('power',     new U32);
        $this->attach('lastLogin', new U32);
        $this->attach('leader',    new LeaderInfo);
        $this->attach('score',     new U32);
    }

    /**
     * @param GuildMember $member
     * @return void
     */
    public function fromGuildMemberObject($member)
    {
        $this->pid->intval($member->pid);
        $this->post->intval($member->getPost());
        $pl = $member->player->getLineup(1);
        $this->lvlsum->intval($pl->lvlsum);
        $this->power->intval($pl->power);
        $this->lastLogin->intval((int)($member->player->lastLogin/1000000));
        $this->leader->fromPlayerObject($member->player);
        $this->score->intval($member->cont-$member->contused);
    }
}
