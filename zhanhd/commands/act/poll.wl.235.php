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
use Zhanhd\ReqRes\Act\Request,
    Zhanhd\ReqRes\Act\DiaRec\Response as DiaRecResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\Activity,
    Zhanhd\Object\ActivityPlan,
    Zhanhd\Object\ActivityHistory;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $type = $request->type->intval();
    $ap = new ActivityPlan;
    if (false === $ap->findByType($this->ustime/1000000, $type)) {
        return $c->addReply($this->errorResponse->error('notfound activity-plan'));
    }

    if ($ap->type == Activity::TYPE_DIAREC) {
        $r = new DiaRecResponse;
        $r->type->intval($ap->type);
        $ah = new ActivityHistory;
        $ah->find($ap->id, $p->id);
        $r->times->intval($ah->score);
        $actdiarecs = Store::get('actdiarec');
        $r->status->resize(count($actdiarecs));
        $i = 0;
        foreach ($actdiarecs as $diarec) {
            $r->status->get($i)->rid->intval($diarec->id);
            $r->status->get($i)->got->intval($ah->profile->{$diarec->id});
            $i++;
        }
        $c->addReply($r);
    }
};
