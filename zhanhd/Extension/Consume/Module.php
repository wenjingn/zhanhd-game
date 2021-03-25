<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Consume;

/**
 *
 */
use Zhanhd\ReqRes\PropUse\PropRemainResponse,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Config\Store;

/**
 *
 */
class Module
{
    /**
     * @return void
     */
    public static function aspect($c, $consume, $notifyResourceChange = true)
    {
        $p = $c->local->player;
        $resourceChanged = false;
        foreach ($consume as $k => $v) {
            $o = Store::get('entity', $k);
            if ($o->isProp()) {
                $p->profile->$k -= $v;
                $r = new PropRemainResponse;
                $r->propId->intval($k);
                $r->num->intval($p->profile->$k);
                $c->addReply($r);
            } else if ($o->isMoney()) {
                $p->decrGold($v);
                $resourceChanged = true;
            } else if ($o->isResource()) {
                $p->profile->$k -= $v;
                $resourceChanged = true;
            }
        }
        
        if ($resourceChanged && $notifyResourceChange) {
            $r = new ResourceResponse;
            $r->retval->fromOwnerObject($p);
            $c->addReply($r);
        }
    }
}
