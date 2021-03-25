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
    System\ReqRes\Str,
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
class SimpleInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('gid',  new U64);
        $this->attach('name', new Str);
        $this->attach('lvl', new U16);
        $this->attach('memnum', new U32);
        $this->attach('flag', new U16);
        $this->attach('leader', new LeaderInfo);
        $this->attach('bulletin', new Str);
    }

    /**
     * @param Guild $guild
     * @return void
     */
    public function fromGuildObject($guild)
    {
        $this->gid->intval($guild->id);
        $this->name->strval($guild->name);
        $this->lvl->intval($guild->lvl);
        $this->memnum->intval($guild->memnum);
        $this->flag->intval(0);
        $president = $guild->getPresident();
        $this->leader->fromPlayerObject($president->player);
        $this->bulletin->strval($guild->bulletin);
    }
}
