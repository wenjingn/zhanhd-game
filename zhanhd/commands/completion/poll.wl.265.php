<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client;

/**
 *
 */
use Zhanhd\ReqRes\Completion\Poll\Request,
    Zhanhd\ReqRes\Completion\Poll\Response,
    Zhanhd\Config\Store;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $type = $request->type->intval();
    $r = new Response;
    if ($type == Request::TYPE_TASK) {
        $rewards = Store::get('completionReward', $type);
        $r->status->resize(count($rewards));
        $i = 0;
        foreach ($rewards as $o) {
            $ck = $o->counterKey();
            if ($p->counterCycle->completionTask < $o->completion) {
                $status = 0;
            } else if ($p->counterCycle->$ck < 1) {
                $status = 1;
            } else {
                $status = 2;
            }
            $r->status->get($i++)->intval($status);
        }
    } else if ($type == Request::TYPE_ROB) {
        $size = 4;
        $r->status->resize($size);
        for ($i = 0; $i < $size; $i++) {
            $completion = ($i+1)*5;
            $ck = sprintf('completion-%d', $type*100+$i+1);
            if ($p->counterCycle->robSuccessTimes < $completion) {
                $status = 0;
            } else if ($p->counterCycle->$ck < 1) {
                $status = 1;
            } else {
                $status = 2;
            }
            $r->status->get($i)->intval($status);
        }
    } else {
        return $c->addReply($this->errorResponse->error('invalid type'));
    }

    $c->addReply($r);
};
