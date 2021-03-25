<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes;

/**
 *
 */
use System\Stdlib\Object,
    System\ReqRes\Box,
    System\ReqRes\Set,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\Entity\Hero,
    Zhanhd\ReqRes\Entity\Prop,
    Zhanhd\ReqRes\Entity\Entity,
    Zhanhd\ReqRes\Entity\Soul,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity        as SourceEntity,
    Zhanhd\Object\Player\Entity as PlayerEntity;

/**
 *
 */
class RewardInfo extends Box
{
    /**
     *
     * @param  Object $rewards
     * @param  Object $global
     * @return void
     */
    public function fromObject(Object $rewards, $global)
    {
        $this->weapon ->intval($rewards->get(2));
        $this->armor  ->intval($rewards->get(3));
        $this->horse  ->intval($rewards->get(7));
        $this->wood   ->intval($rewards->get(6));
        $this->soldier->intval($rewards->get(4));
        $this->gold   ->intval($rewards->get(10));

        $this->heros->resize($rewards->h->count());
        foreach ($this->heros as $i => $h) {
            $h->fromPlayerEntityObject($rewards->h->get($i), $global);
        }

        $this->items->resize($rewards->e->count());
        foreach ($this->items as $i => $e) {
            $e->peid->intval($rewards->e->get($i)->id);
            $e-> eid->intval($rewards->e->get($i)->eid);
        }

        $this->props->resize($rewards->p->count());
        foreach ($this->props as $i => $p) {
            $p->fromPlayerEntityObject($rewards->p->get($i));
        }

        $this->souls->resize($rewards->s->count());
        foreach ($this->souls as $i => $s) {
            $s->fromObject($rewards->s->get($i));
        }
    }

    /**
     * @param traversable $rewards
     * @param Object $global
     * @return void
     */
    public function fromArray($rewards, $global)
    {
        $map = [
            2  => 'weapon',
            3  => 'armor',
            4  => 'soldier',
            6  => 'wood',
            7  => 'horse',
            10 => 'gold',
        ];

        $heros  = [];
        $equips = [];
        $props  = [];
        $souls  = [];
        foreach ($rewards as $eid => $num) {
            if (null === ($e = Store::get('entity', $eid))) {
                continue;
            }
            if ($e->isProp()) {
                $props[] = [
                    'eid' => $eid,
                    'num' => $num,
                ];
            } else {
                switch ($e->type) {
                case SourceEntity::TYPE_RESOURCE:
                case SourceEntity::TYPE_MONEY:
                    $this->{$map[$eid]}->intval($num);
                    break;

                case SourceEntity::TYPE_HERO:
                    for ($i = 0; $i < $num; $i++) {
                        $heros[] = $e->toPe(0, 0);
                    }
                    break;

                case SourceEntity::TYPE_WEAPON:
                case SourceEntity::TYPE_ARMOR:
                case SourceEntity::TYPE_HORSE:
                case SourceEntity::TYPE_JEWEL:
                    for ($i = 0; $i < $num; $i++) {
                        $equips[] = $e;
                    }
                    break;
                case SourceEntity::TYPE_SOUL:
                    $souls[] = [
                        'eid' => $eid,
                        'num' => $num,
                    ];
                    break;
                }
            }
        }

        $this->heros->resize(count($heros));
        foreach ($this->heros as $i => $o) {
            $o->fromPlayerEntityObject($heros[$i], $global);
        }

        $this->items->resize(count($equips));
        foreach ($this->items as $i => $o) {
            $o->peid->intval(0);
            $o->eid->intval($equips[$i]->id);
        }

        $this->props->resize(count($props));
        foreach ($this->props as $i => $o) {
            $o->eid->intval($props[$i]['eid']);
            $o->num->intval($props[$i]['num']);
        }

        $this->souls->resize(count($souls));
        foreach ($this->souls as $i => $o) {
            $o->eid->intval($souls[$i]['eid']);
            $o->num->intval($souls[$i]['num']);
        }
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('weapon',  new U32);
        $this->attach('armor',   new U32);
        $this->attach('horse',   new U32);
        $this->attach('wood',    new U32);
        $this->attach('soldier', new U32);
        $this->attach('gold',    new U32);

        $this->attach('heros', new Set(new Hero));
        $this->attach('items', new Set(new Entity));
        $this->attach('props', new Set(new Prop));
        $this->attach('souls', new Set(new Soul));
    }
}
