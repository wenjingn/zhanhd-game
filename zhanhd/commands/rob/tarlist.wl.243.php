<?php
/**
 *
 */

/**
 *
 */
use System\Swoole\Client,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\ReqRes\Rob\Tarlist\Request,
    Zhanhd\ReqRes\Rob\Tarlist\Response,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Rob\Tarlist,
    Zhanhd\Object\Player\Rob\Target;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $flag = $request->flag->intval();
    $p = $c->local->player;

    $tarlist = new Tarlist;
    $tarlist->find($p->id);
    if ($flag && $this->ustime < $tarlist->refresh+Tarlist::CDREFRESH) {
        return $c->addReply($this->errorResponse->error('cool down time'));
    }

    if ($flag == 0 && $tarlist->total > 0) {
        $targets = $tarlist->getTargets();
    } else {
        $tarlist->pid = $p->id;
        $tarlist->dropTargets();
        $ranlist = Player::randomIds($this->pdo, Tarlist::MAXCOUNT, ['id!='.$p->id]);
        if ($flag) {
            $tarlist->refresh = $this->ustime;
        }
        $targets = $tarlist->genTargets($ranlist);
    }

    $r = new Response;
    $r->targets->resize($tarlist->total);
    foreach ($r->targets as $i => $o) {
        $o->fromObject($targets->get($i), $p->getLineup(1)->power);
    }
    $cd = Tarlist::CDREFRESH+$tarlist->refresh-$this->ustime;
    $cd = $cd > 0 ? (int)($cd/1000000) : 0;
    $r->refreshCD->intval($cd);
    $r->robSuccessTimes->intval((int)$p->counterCycle->robSuccessTimes);
    $c->addReply($r);
};
