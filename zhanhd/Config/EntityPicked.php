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
use System\Object\ConfigObject,
    System\Stdlib\Object;

/**
 *
 */
class EntityPicked extends ConfigObject
{
    /**
     * @struct self
     * 
     * @id  integer
     * @tag integer
     * @pick integer
     * @void integer
     * @deep integer
     * @source array( (integer)(eid|epid|gid) => (integer)prob )
     */

    /**
     *
     * @param  boolean $once
     * @return Object
     */
    public function pick($once = false)
    {
        $tags = [];
        if (false === $once) {
            $pick = $this->pick;
        } else if ($this->pick > 0) {
            $pick = 1;
        } else if ($this->pick < 0) {
            $pick = -1;
        } else {
            $pick = 0;
        }

        if ($pick == 0) {
            // never reach here
        } else if ($pick > 0) {
            $seed = array_sum($this->source) + $this->void;

            while ($seed > $this->void && 0 < $pick--) {
                $rand = mt_rand(1, $seed);
                foreach ($this->source as $tag => $prob) {
                    if ($prob <= 0) {
                        continue;
                    }

                    $rand -= $prob;
                    if ($rand > 0) {
                        continue;
                    }

                    if (isset($tags[$tag])) {
                        $tags[$tag] += 1;
                    } else {
                        $tags[$tag]  = 1;
                    }

                    break;
                }
            }
        } else {
            foreach ($this->source as $tag => $prob) {
                while (0 < $prob-- && 0 > $pick++) {
                    if (isset($tags[$tag])) {
                        $tags[$tag] += 1;
                    } else {
                        $tags[$tag]  = 1;
                    }
                }
            }
        }
        
        return $this->pickTags($tags);
    }

    /**
     *
     * @param  array $tags
     * @return Object
     */
    private function pickTags(array $tags)
    {
        $retval = [];
        foreach ($tags as $tag => $num) {
            if (Store::has('entity', $tag)) {
                if (isset($retval[$tag])) {
                    $retval[$tag] += $num;
                } else {
                    $retval[$tag]  = $num;
                }

                continue;
            }

            $p = Store::get('edrop', $tag);
            if ($p && $p->deep < $this->deep) {
                for ($i = 0; $i < $num; $i++) {
                    foreach ($p->pick(true) as $eid => $cnt) {
                        if (isset($retval[$eid])) {
                            $retval[$eid] += $cnt;
                        } else {
                            $retval[$eid]  = $cnt;
                        }
                    }
                }

                continue;
            }

            $g = Store::get('egroup', $tag);
            if ($g) {
                $picked = $g->pick($num);
                foreach ($picked as $eid => $n) {
                    if (isset($retval[$eid])) {
                        $retval[$eid] += $n;
                    } else {
                        $retval[$eid] = $n;
                    }
                }
                continue;
            }
        }

        return (new Object)->import($retval)->map(function($num, $eid) {
            return [
                'e' => Store::get('entity', $eid),
                'n' => $num,
            ];
        });
    }
}
