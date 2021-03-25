<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Stdlib\Object,
    System\Swoole\Client,
    System\ReqRes\Int\U16 as Request;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\ReqRes\Guide\Response,
    Zhanhd\ReqRes\Guide\Reward\Response as RewardResponse,
    Zhanhd\ReqRes\ResourceRecruitResponse,
    Zhanhd\ReqRes\Recruit\HeroResponse,
    Zhanhd\ReqRes\Recruit\EquipResponse,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\ReqRes\PropUse\PropRemainResponse,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $GuideMaxId = 11;
    $guideId = $request->intval();
    $p = $c->local->player;
    if (!$p->profile->guideFlag) {
        return $c->addReply($this->errorResponse->error('invalid guide sequence'));
    }
    if ($p->profile->guideId != $guideId - 1) {
        return $c->addReply($this->errorResponse->error('invalid guide sequence'));
    }

    $resourceChanged = false;
    switch ($guideId) {
    case 3:
    case 4:
        if ($guideId == 3) {
            $require = [
                2 => 20,
                3 => 20,
                4 => 20,
                7 => 20,
            ];
            $eid = 1101302;
        } else {
            $require = [
                2 => 30,
                3 => 20,
                4 => 20,
                7 => 20,
            ];
            $eid = 1101202;
        }
        foreach ($require as $k => $v) {
            if ($p->profile->$k >= $v) {
                $p->profile->$k -= $v;
            }
        }
        $p->profile->save();
        $resourceChanged = true;
        
        $hero = new Object;
        $p->increaseEntity((new Object)->import([
            $eid => [
                'e' => Store::get('entity', $eid),
                'n' => 1,
            ]
        ]), function($pe, $hero) {
            $hero->set(null, $pe);
        }, $hero);
        $r = new ResourceRecruitResponse;
        $r->retval->resize(1);
        $r->retval->get(0)->fromPlayerEntityObject($hero->get(0), $this);
        $c->addReply($r);
        break;
    case 5:
        if ($p->gold >= 200) {
            $p->gold -= 200;
        }
        $p->recent->freeRecruit2030 = $this->ustime;
        $p->save();
        $resourceChanged = true;

        $hero = new Object;
        $p->increaseEntity((new Object)->import([
            1101301 => [
                'e' => Store::get('entity', 1101301),
                'n' => 1,
            ]
        ]), function($pe, $hero) {
            $hero->set(null, $pe);
        }, $hero);
        $r = new HeroResponse;
        $r->retval->resize(1);
        $r->retval->get(0)->fromPlayerEntityObject($hero->get(0), $this);
        $r->freeCD->intval(86400*2);
        $c->addReply($r);
        break;
    case 8:
        $require = [
            2 => 200,
            410103 => 1,
        ];
        $eid = 210101;
        foreach ($require as $k => $v) {
            if ($p->profile->$k >= $v) {
                $p->profile->$k -= $v;
            }
        }
        $p->profile->save();
        $resourceChanged = true;
        $r = new PropRemainResponse;
        $r->propId->intval(410103);
        $r->num->intval($p->profile->{410103});
        $c->addReply($r);

        $equip = new Object;
        $p->increaseEntity((new Object)->import([
            $eid => [
                'e' => Store::get('entity', $eid),
                'n' => 1,
            ],
        ]), function($pe, $equip) {
            $equip->set(null, $pe);
        }, $equip);

        $r = new EquipResponse;
        $r->retval->fromPlayerEntityObject($equip->get(0), $this);
        $c->addReply($r);
        break;
    case 11:
        $rewards = [
            6 => 288,
            410106 => 2,
            10 => 66,
        ];
        $r = new RewardResponse;
        RewardModule::aspect($p, $rewards, $r->reward, $c, $this);
        $c->addReply($r);
        break;
    }

    if ($resourceChanged) {
        $r = new ResourceResponse;
        $r->retval->fromOwnerObject($p);
        $c->addReply($r);
    }


    $p->profile->guideId = $guideId;
    if ($p->profile->guideId == $GuideMaxId) {
        $p->profile->guideFlag = 0;
    }
    $p->profile->save();

    $r = new Response;
    $r->guideId->intval($guideId);
    $c->addReply($r);
};
