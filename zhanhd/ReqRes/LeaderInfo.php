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
    System\ReqRes\Str,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Player;

/**
 *
 */
class LeaderInfo extends Box
{
    /**
     *
     * @return void
     * @todo   setup flags
     */
    public function fromPlayerObject(Player $p)
    {
        $this->name->strval($p->name);
        $this->sex ->intval($p->profile->sex ?: 0);

        $this->img ->fromOwnerObject($p);
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('name', new Str);
        $this->attach('exp',  new U32);
        $this->attach('sex',  new U32);
        $this->attach('img',  new Leader\Image);
    }
}
