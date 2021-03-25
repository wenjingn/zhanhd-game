<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Reward;

/**
 *
 */
use System\Stdlib\Object,
    System\Swoole\Client;

/**
 *
 */
use Zhanhd\ReqRes\RewardInfo,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity as SourceEntity,
    Zhanhd\Object\Player;

/**
 *
 */
class Module
{
    /**
     * @param Player $p
     * @param traversable $rewards
     * @param RewardInfo $rewardInfo
     * @param Client $c
     * @param Object $global
     * @return void
     */
    public static function aspect(Player $p, $rewards, RewardInfo $rewardInfo, Client $c, $global)
    {
        $items = new Object; 
        foreach ($rewards as $eid => $num) {
            if (null === ($e = Store::get('entity', $eid))) {
                continue;
            }

            $items->set($eid, [
                'e' => $e,
                'n' => $num,
            ]);
        }
        
        $notifyResourceChange = false;
        $rewards = (new Object)->import([
            'h' => [],
            'e' => [],
            'p' => [],
            's' => [],
        ]);
        $p->increaseEntity($items, function($pe) use ($rewards, &$notifyResourceChange) {
            if ($pe->e->isProp()) {
                $rewards->p->set(null, $pe);
            } else {
                switch ($pe->e->type) {
                case SourceEntity::TYPE_RESOURCE:
                case SourceEntity::TYPE_MONEY:
                    $notifyResourceChange = true;
                    $rewards->{$pe->e->id} = $pe->n;
                    break;
                case SourceEntity::TYPE_HERO:
                    $rewards->h->set(null, $pe);
                    break;
                case SourceEntity::TYPE_WEAPON:
                case SourceEntity::TYPE_ARMOR:
                case SourceEntity::TYPE_HORSE:
                case SourceEntity::TYPE_JEWEL:
                    $rewards->e->set(null, $pe);
                    break;
                case SourceEntity::TYPE_SOUL:
                    $rewards->s->set(null, $pe);
                    break;
                }
            }
        });

        $rewardInfo->fromObject($rewards, $global);
        if ($notifyResourceChange) {
            $resourceResponse = new ResourceResponse;
            $resourceResponse->retval->fromOwnerObject($p);
            $c->addReply($resourceResponse);
        }
    }
}
