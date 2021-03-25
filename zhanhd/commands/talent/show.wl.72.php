<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\ReqRes\Talent\Show\Request,
    Zhanhd\ReqRes\Talent\Show\Response,
    Zhanhd\Config\EntityPicked,
    Zhanhd\Config\Entity        as SourceEntity,
    Zhanhd\Config\WeekMission,
    Zhanhd\Object\Player\Entity as PlayerEntity,
    Zhanhd\Object\Player\Counter\Cycle as PlayerCounterCycle,
    Zhanhd\Extension\WeekMission\Module as WeekMissionModule;

/**
 *
 */
$TALENT_SHOW_RECRUIT = [
    30  => [
        'void'   => 40,
        'source' => [
            302001 => 60,
        ]
    ],
    75  => [
        'void'   => 0,
        'source' => [
            302001 => 50,
            302002 => 35,
            302003 => 15,
        ]
    ],
    99  => [
        'void'   => 0,
        'source' => [
            302002 => 45,
            302003 => 55,
        ]
    ],
    100 => [
        'void' => 0,
        'source' => [
            302004 => 99,
            302005 => 1,
        ]
    ],

];

/**
 *
 */
return function(Client $c) use ($TALENT_SHOW_RECRUIT) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    /**
     * check
     */
    if ($p->counterCycle->talent >= PlayerCounterCycle::DAILY_TALENT_LIMIT) {
        return $c->addReply($this->errorResponse->error('times of talent-show reach the maximum'));
    }

    $size = $request->talents->size();
    if ($size < 1) {
        return $c->addReply($this->errorResponse->error('talent-show provide nothing'));
    } else if ($size > 100) {
        return $c->addReply($this->errorResponse->error('talent-show provide too mush'));
    }

    $talents = [];
    foreach ($request->talents as $o) {
        $peid = $o->intval();
        if (isset($talents[$peid])) {
            return $c->addReply($this->errorResponse->error('provide duplicate pe'));
        }

        $pe = new PlayerEntity;
        if (false === $pe->findByPid($peid, $p->id)) {
            return $c->addReply($this->errorResponse->error('hero not found'));
        }

        if ($pe->e->type <> SourceEntity::TYPE_HERO) {
            return $c->addReply($this->errorResponse->error('pe must be hero'));
        }

        if ($pe->e->rarity < 1) {
            return $c->addReply($this->errorResponse->error('rarity not enough'));
        }

        if ($pe->flags == PlayerEntity::FLAG_INUSE) {
            return $c->addReply($this->errorResponse->error('pe already inuse'));
        }

        $talents[$peid] = $pe;
    }

    /**
     * do it
     */
    $score = 0;
    foreach ($talents as $pe) {
        $score += $pe->e->getTalentScore();
        $pe->drop();
    }
    $score = min($score, 100);

    $r = new Response;

    $r->eliminated->resize(count($talents));
    reset($talents);
    foreach ($r->eliminated as $o) {
        $pe = current($talents);
        $o->intval($pe->id);
        next($talents);
    }

    foreach ($TALENT_SHOW_RECRUIT as $s => $recruit) {
        if ($score <= $s) {
            $ep = new stdClass;
            $ep->pick = 1;
            $ep->void = $recruit['void'];
            $ep->deep = 255;
            $ep->source = $recruit['source'];
            
            $ep = new EntityPicked($ep);
            $picked = $ep->pick();
            if ($picked->count()) {
                $r->heros->resize(1);
                $e = $picked->head()->e;
                $p->increaseEntity((new Object)->import([
                    $e->id => [
                        'e' => $e,
                        'n' => 1,
                    ]            
                ]), function($pe, $r, $global) {
                    $r->heros->get(0)->fromPlayerEntityObject($pe, $global);
                }, $r, $this);
            }

            break;
        }
    }
    $p->counterCycle->talent++;
    $p->counterCycle->save();
    $p->counter->talent++;
    $p->counter->save();
    $p->counterWeekly->talent++;
    $p->counterWeekly->save();
    WeekMissionModule::trigger($p, $this, WeekMission::TYPE_TALSHOW, $p->counterWeekly->talent);
    $r->times->intval($p->counterCycle->talent);

    $c->addReply($r);
};
