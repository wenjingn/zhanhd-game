<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config\Instance;

/**
 *
 */
use System\Object\ConfigObject;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Object\Player\Lineup      as PlayerLineup,
    Zhanhd\Object\Player\Lineup\Hero as PlayerLineupHero;

/**
 *
 */
class Event extends ConfigObject
{
    /**
     * @struct self
     * @evt   integer
     * @flags integer
     * @fmt   integer
     * @exp   integer
     * @prev  [ (integer)prev => 1 ]
     * @next  [ (integer)next => (integer)prob ]
     * @npc   [ (integer)pos  => InsEvtNpc ]
     * @drop  [ (integer)idx  => InsEvtDropItem ]
     */

    /**
     * @struct InsEvtNpc
     * @eid integer
     * @lvl integer
     * @ehc integer
     */

    /**
     * @struct InsEvtDropItem
     * @first integer
     * @prob  integer
     * @items [ (integer)eid => (integer)num ]
     */

    /**
     * @const integer
     */
    const TYPE_FIGHT    = 1;
    const TYPE_RESOURCE = 2;
    const TYPE_BRANCH   = 4;
    const TYPE_RANDOM   = 8;
    const RESERVED_A    = 16;
    const RESERVED_B    = 32;
    const RESERVED_C    = 64;
    const TYPE_HEAD     = 128;
    const TYPE_TAIL     = 256;
    const TYPE_FINAL    = 512;

    const TYPE_MASK     = 127;
    
    /**
     * @return boolean
     */
    public function ishead()
    {
        return $this->flags & self::TYPE_HEAD;
    }

    /**
     * @return boolean
     */
    public function istail()
    {
        return $this->flags & self::TYPE_TAIL;
    }

    /**
     * @return integer
     */
    public function getType()
    {
        return $this->flags & self::TYPE_MASK;
    }

    /**
     * @return integer
     */
    public function getRandom()
    {
        if (!($this->flags & self::TYPE_RANDOM)) {
            return false;
        }

        $seed = array_sum($this->next);
        $rand = mt_rand(1, $seed);
        foreach ($this->next as $evt => $prob) {
            if ($rand <= $prob) {
                return $evt;
            }

            $rand -= $prob;
        }
    }

    /**
     * @param boolean $isMember
     * @return integer
     */
    public function getExp($isMember = false)
    {
        if (!($this->flags & self::TYPE_FIGHT)) {
            return false;
        }

        if ($isMember) {
            return $this->exp + ($this->exp >> 1);
        }
        return $this->exp;
    }

    /**
     * @return PlayerLineup
     */
    public function getNpcLineup()
    {
        if (!($this->flags & self::TYPE_FIGHT)) {
            return false;
        }

        $l = new PlayerLineup;
        $l->gid = 1;
        $l->fid = $this->fmt;
        
        foreach ($this->npc as $pos => $npc) {
            if (null === ($e = Store::get('entity', $npc->eid))) {
                continue;
            }

            $pe = $e->toPe($npc->lvl, $npc->ehc);
            $pe->id  = PHP_INT_MAX - ($this->evt * 10 + $pos);
            $pe->pid = $this->evt;

            $plh = new PlayerLineupHero;
            $plh->pe = $pe;
            $plh->gid = 1;
            $plh->pos = $pos;
            $plh->peid= $pe->id;

            $l->heros->set(null, $plh);
        }

        if ($l->fid) {
            $l->f = Store::get('formation', $l->fid);
        }
        
        return $l;
    }

    /**
     * @param boolean $isFirst
     * @return array
     */
    public function drop($isFirst)
    {
        if (false === isset($this->drop)) {
            return false;
        }

        if (empty($this->drop)) {
            return false;
        }

        $ret = [];
        foreach ($this->drop as $drop) {
            if ($drop->first != $isFirst) {
                continue;
            }

            if (mt_rand(1, 10000) > $drop->prob) {
                continue;
            }

            if (false === isset($drop->items) || empty($drop->items)) {
                continue;
            }

            foreach ($drop->items as $eid => $num) {
                if (Store::has('entity', $eid)) {
                    if (false === isset($ret[$eid])) {
                        $ret[$eid] = $num;
                    } else {
                        $ret[$eid]+= $num;
                    }
                } else if (Store::has('egroup', $eid)) {
                    $picks = Store::get('egroup', $eid)->pick($num);
                    foreach ($picks as $eid => $num) {
                        if (false === isset($ret[$eid])) {
                            $ret[$eid] = $num;
                        } else {
                            $ret[$eid]+= $num;
                        }
                    }
                }
            }
        }

        return $ret;
    }
}
