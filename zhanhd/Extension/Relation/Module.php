<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Relation;

/**
 *
 */
use System\Swoole\Client;

/**
 *
 */
use Zhanhd\ReqRes\Relation\Confirm\Response;

/**
 *
 */
class Module
{
    /**
     * @param Client $c
     * @param Object $global
     * @return void
     */
    public static function aspect(Client $c, $global)
    {
        $confirms = $c->local->player->getRelationConfirms();
    
        if ($n = $confirms->count()) {
            $r = new Response;
            $r->friends->resize($n);
            $i = 0;
            foreach ($confirms as $pr) {
                $r->friends->get($i)->fromRelationObject($pr, $global);
                $i++;
            }
            $c->addReply($r);
        }
    }
}
