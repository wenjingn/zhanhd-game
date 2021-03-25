<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config;

/**
 *
 */
use System\Object\ConfigObject;

/**
 *
 */
class Reward extends ConfigObject
{
    /**
     * @struct self
     *
     * @id  integer
     * @tag string
     *
     * @ranks  array((integer)index => (stdClass)RewardRank)
     * @scores array((integer)index => (stdClass)RewardScore)
     */

    /**
     * @struct RewardRank
     *
     * @index    integer
     * @lowRank  integer
     * @source   array((integer)eid|gid => (integer)num)
     */

    /**
     * @struct RewardScore
     *
     * @index  integer
     * @requre integer
     * @source array((integer)eid|gid => (integer)num)
     */

    /**
     * @param integer $rank
     * @return array
     */
    public function getRankRewards($rank)
    {
        if ($rank === null) {
            return [];
        }
        if (false === isset($this->ranks)) {
            return [];
        }

        foreach ($this->ranks as $o) {
            if ($o->lowRank == 0) {
                return $o->source;
            }

            if ($rank <= $o->lowRank) {
                return $o->source;
            }
        }

        return [];
    }

    /**
     * @param integer $rank
     * @return array
     */
    public function getRankCoherences($rank)
    {
        if ($rank === null) {
            return [];
        }
        if (false === isset($this->ranks)) {
            return [];
        }

        foreach ($this->ranks as $o) {
            if ($o->lowRank == 0) {
                if (isset($o->coherence) && is_array($o->coherence)) {
                    return $o->coherence;
                } else {
                    return [];
                }
            }

            if ($rank <= $o->lowRank) {
                if (isset($o->coherence) && is_array($o->coherence)) {
                    return $o->coherence;
                } else {
                    return [];
                }
            }
        }

        return [];
    }

    /**
     * @param integer $score
     * @return array
     */
    public function getScoreRewards($score)
    {
        if ($score === null) {
            return [];
        }
        if (false === isset($this->scores)) {
            return [];
        }
        
        $ret = [];
        foreach ($this->scores as $o) {
            if ($score >= $o->require) {
                return $o->source;
            }
        }
        return [];
    }

    /**
     * @param integer $rank
     * @param integer $score
     * @return array
     */
    public function getAllRewards($rank, $score)
    {
        $rewards1 = $this->getRankRewards($rank);
        $rewards2 = $this->getScoreRewards($score);
        foreach ($rewards2 as $k => $v) {
            if (isset($rewards1[$k])) {
                $rewards1[$k]+= $v;
            } else {
                $rewards1[$k] = $v;
            }
        }
        return $rewards1;
    }
}
