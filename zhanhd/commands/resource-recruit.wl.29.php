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
use Zhanhd\ReqRes\ResourceRecruitRequest    as Request,
    Zhanhd\ReqRes\ResourceRecruitResponse   as Response,
    Zhanhd\ReqRes\SysMsgResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity                    as SourceEntity,
    Zhanhd\Config\NewzoneMission,
    Zhanhd\Extension\Achievement\Module     as AchievementModule,
    Zhanhd\Extension\NewzoneMission\Module  as NewzoneMissionModule,
    Zhanhd\Extension\Check\Module           as CheckModule,
    Zhanhd\Extension\Guide                  as GuideModule,
    Zhanhd\ReqRes\PropUse\PropRemainResponse,
    Zhanhd\ReqRes\Building\ResourceResponse as BuildingResourceResponse;

/**
 *
 * @param array   $rr
 * @param integer $total
 * @return mixed
 */
$searchResourceRecruitCallback = function (array $rr, $total) {
    $h = count($rr) - 1;
    $l = 0;

    if ($rr[$l]->total <= $total) {
        return $rr[$l];
    }

    if ($rr[$h]->total > $total) {
        return false;
    }

    while ($l <= $h) {
        $m = ($l + $h) >> 1;
        $x = $rr[$m]->total - $total;

        if ($x == 0) {
            return $rr[$m];
        }

        if ($x > 0) {
            if ($rr[$m + 1]->total < $total) {
                return $rr[$m + 1];
            }

            $l = $m + 1;
        } else {
            if ($rr[$m - 1]->total > $total) {
                return $rr[$m];
            }

            $h = $m - 1;
        }
    }
};

/**
 * 很恶心, 不是吗?
 */
return function(Client $c) use ($searchResourceRecruitCallback) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    if (false === CheckModule::packageAspect($c, $this, CheckModule::CHECK_PACKAGE_HERO)) {
        return;
    }

    if (null === ($rr = Store::get('ResourceRecruit')) || null === ($rrp = Store::get('ResourceRecruitPercentage'))) {
        return $c->addReply($this->errorResponse->error('notfound goods'));
    }

    $p = $c->local->player;
    $x = array(
        'soldier' => 4,
        'weapon'  => 2,
        'armor'   => 3,
        'horse'   => 7,
    );

    /* check resources */
    $red = array(); $t = 0; foreach ($x as $name => $key) {
        /* at least 20 */
        if (($v = $request->$name->intval()) < 20) {
            return $c->addReply($this->errorResponse->error('notfound goods'));
        }

        if ($v > $p->profile->$key) {
            return $c->addReply($this->errorResponse->error('resource not enough'));
        }

        $t += $v;
        // $p->profile->$key -= $v;
        $red[$key] = $v;
    }

    if (false === ($o = $searchResourceRecruitCallback($rr, $t))) {
        return $c->addReply($this->errorResponse->error('notfound goods'));
    }

    /* 虎符 */
    $usingPropEid = $k = 410102;
    if ($o->prop > $p->profile->$k) {
        return $c->addReply($this->errorResponse->error('resource not enough'));
    } else {
        // $p->profile->$k -= $o->prop;
        $red[$k] = $o->prop;
    }

    if (false === ($k = $o->pick())) {
        return $c->addReply($this->errorResponse->error('notfound goods'));
    }

    if ($k == 10000) {
        /* 特殊掉落 */
    } else {
        /* 决定几星什么兵种 */{
            $seed = 0; $sets = array(); foreach ($x as $name => $v) {
                if (false === ($o = $searchResourceRecruitCallback($rrp[$name], $request->$name->intval()))) {
                    return $c->addReply($this->errorResponse->error('notfound goods'));
                }

                $seed += $o->prob;
                $sets[$o->army] = $o->prob;
            }

            if ($seed == 0) {
                return $c->addReply($this->errorResponse->error('notfound goods'));
            }

            $rand = mt_rand(1, $seed); $found = false; foreach ($sets as $army => $prob) {
                $rand -= $prob;
                if ($rand > 0) {
                    continue;
                }

                $found = true;
                break;
            }

            if (false === $found) {
                return $c->addReply($this->errorResponse->error('notfound goods'));
            }
        }

        $k = sprintf('%s%02d', substr($army, 1), $k);
    }

    if (null === ($rrg = Store::get('ResourceRecruitGroup', $k))) {
        return $c->addReply($this->errorResponse->error('notfound goods'));
    }

    /* 随机选一个武将 */{
        $seed = 0; foreach ($rrg as $eid => $prob) {
            $seed += $prob;
        }

        if ($seed == 0) {
            return $c->addReply($this->errorResponse->error('notfound goods'));
        }

        $rand = mt_rand(1, $seed); $found = false; foreach ($rrg as $eid => $prob) {
            $rand -= $prob;
            if ($rand > 0) {
                continue;
            }

            $found = true;
            break;
        }

        if (false === $found) {
            return $c->addReply($this->errorResponse->error('notfound goods'));
        }
    }

    if (false === Store::has('entity', $eid)) {
        $this->debug($c, 'k=%s e=%s', $k, $eid);
        return $c->addReply($this->errorResponse->error('notfound goods'));
    }

    $p->counterCycle->resourceRecruit++;
    $p->counter->resourceRecruit++;

    /* new zone mission */
    NewzoneMissionModule::trigger($p, $this, NewzoneMission::TYPE_RECRUITHERO, $p->counter->resourceRecruit);

    // update pp resource
    foreach ($red as $k => $v) {
        $p->profile->$k -= $v;
    }

    $p->save();

    $h = new Object;
    $p->increaseEntity((new Object)->import(array(array(
        'e' => Store::get('entity', $eid),
        'n' => 1,
    ))), function($pe, $c, $p) use ($h) {
        switch ($pe->e->type) {
        case SourceEntity::TYPE_HERO:
            $h->set(null, $pe);
            (new AchievementModule($c->local->player))->trigger((new Object)->import(array(
                'cmd'    => 'recruit',
                'strval' => 'hero',
            )));

            /* broadcast */
            if ($pe->e->rarity == 4) {
                $smr = new SysMsgResponse;
                $smr->format(1, 1, array($p->name, '资源招募', array($pe->e->tag, 'orange')));
                $this->task('broadcast-global', $smr);
            } else if ($pe->e->rarity == 5) {
                $smr = new SysMsgResponse;
                $smr->format(1, 2, array($p->name, '资源招募', array($pe->e->tag, 'red')));
                $this->task('broadcast-global', $smr);
            }

            break;
        }

        // logger
        $c->local->logger->set(null, array(
            'eid'  => $pe->e->id,
            'cnt'  => 1,
            'peid' => $pe->id,
        ));
    }, $c, $p);

    $r = new Response;
    $r->retval->resize(1);
    $r->retval->get(0)->fromPlayerEntityObject($h->get(0), $this);
    $c->addReply($r);

    $r = new PropRemainResponse;
    $r->propId->intval($usingPropEid);
    $r->num   ->intval($p->profile->$usingPropEid);
    $c->addReply($r);

    $r = new BuildingResourceResponse;
    $r->retval->fromOwnerObject($p);
    $c->addReply($r);
    if ($p->profile->guideId < 3) {
        GuideModule::aspect($c, 3);
    } else {
        GuideModule::aspect($c, 4);
    }
};
