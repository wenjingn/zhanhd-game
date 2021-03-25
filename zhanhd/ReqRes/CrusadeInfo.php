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
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Player\Crusade as PlayerCrusade;

/**
 *
 */
class CrusadeInfo extends Box
{
    /**
     *
     * @return void
     */
    public function fromPlayerCrusadeObject(PlayerCrusade $o)
    {
        $this->cid->intval($o->cid);
        $this->aid->intval($o->crusade->act);
        $this->seq->intval($o->crusade->seq);
        $this->gid->intval($o->gid);

        switch ($o->flags) {
        case PlayerCrusade::FLAG_ATTACKING:
            $this->flag->intval(2);
            break;

        case PlayerCrusade::FLAG_ACCEPTING:
            $this->flag->intval(3);
            break;

        case PlayerCrusade::FLAG_RECALLING:
        case PlayerCrusade::FLAG_DONE:
            $this->flag->intval(1);
            break;
        }

        $this->time->intval($o->remaining());
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('cid', new U32);
        $this->attach('aid', new U32);
        $this->attach('seq', new U32);
        $this->attach('gid', new U32);

        $this->attach('flag', new U32);
        $this->attach('time', new U32);
    }
}
