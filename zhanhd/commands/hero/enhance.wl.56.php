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
use Zhanhd\ReqRes\Hero\Enhance\Request,
    Zhanhd\ReqRes\Hero\Enhance\Response;

/**
 *
 */
use Zhanhd\Config\Enhance,
    Zhanhd\Config\Entity         as SourceEntity,
    Zhanhd\Object\Player\Entity  as PlayerEntity;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $peid = $request->peid->intval();

    /**
     * check
     */
    $pe = new PlayerEntity;
    if (false === $pe->findByPid($peid, $p->id)) {
        return $c->addReply($this->errorResponse->error('pe not found'));
    }

    if ($pe->e->type <> SourceEntity::TYPE_HERO) {
        return $c->addReply($this->errorResponse->error('pe must be hero'));
    }

    $oSkillPoint = $skillPoint = $pe->skill->skillPoint();
    $maxSkillPoint = $pe->maxSkillPoint();
    if ($skillPoint >= $maxSkillPoint) {
        return $c->addReply($this->errorResponse->error('already max-level'));
    }

    if (0 === $request->consumptions->size()) {
        return $c->addReply($this->errorResponse->error('enhance provide nothing'));
    }

    $eaten  = [];
    foreach ($request->consumptions as $consumption) {
        $eatid = $consumption->intval();
        if ($eatid === $peid) {
            continue;
        }

        if (isset($eaten[$eatid])) {
            continue;
        }

        $eat = new PlayerEntity;
        if (false === $eat->findByPid($eatid, $p->id)) {
            continue;
        }

        if ($eat->e->id <> $pe->e->id) {
            return $c->addReply($this->errorResponse->error('provide hero must be same with pe'));
        }

        if ($eat->flags == PlayerEntity::FLAG_INUSE) {
            return $c->addReply($this->errorResponse->error('pe already inuse'));
        }

        $skillPoint+= $eat->skill->skillPoint()+1;
        $eaten[$eat->id] = $eat;
        if ($skillPoint >= $maxSkillPoint) {
            break;
        }
    }
    
    $upgradableSkills = [];
    $total = 0;
    foreach ($pe->getUpgradableSkills() as $sid => $n) {
        $total += $n;
        $upgradableSkills = array_pad($upgradableSkills, $total, $sid);
    }
    shuffle($upgradableSkills);
    $upgraded = array_slice($upgradableSkills, 0, $skillPoint - $oSkillPoint);
    foreach ($upgraded as $sid) {
        if ($pe->skill->$sid) {
            $pe->skill->$sid++;
        } else {
            $pe->skill->$sid = 2;
        }
    }
    $pe->save();
    foreach ($eaten as $o) {
        $o->drop();
    }

    /**
     * send response
     */
    $eaten = array_values($eaten);
    $r = new Response;
    $r->peid->intval($peid);
    $revskills = $pe->e->getRevSkills();
    $r->skillLevels->resize(count($revskills));
    foreach ($r->skillLevels as $k => $o) {
        $o->intval($pe->skill->{$revskills[$k]});
    }
    $r->consumptions->resize(count($eaten));
    foreach ($r->consumptions as $k => $o) {
        $o->intval($eaten[$k]->id);
    }
    $c->addReply($r);
};
