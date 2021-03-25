<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension;

/**
 *
 */
use System\Swoole\Client;

/**
 *
 */
use Zhanhd\ReqRes\Guide\Response;

/**
 *
 */
class Guide
{
    /**
     * @param Client $c
     * @param integer $step
     * @return void
     */
    public static function aspect(Client $c, $step)
    {
        $p = $c->local->player;
        if (!$p->profile->guideFlag) return;
        if ($p->profile->guideId != $step-1) return;
        $p->profile->guideId++;
        $p->profile->save();
        $r = new Response;
        $r->guideId->intval($p->profile->guideId);
        $c->addReply($r);
    }
}
