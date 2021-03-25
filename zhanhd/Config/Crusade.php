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
use stdClass,
    ReflectionMethod;

/**
 *
 */
use System\Object\ConfigObject,
    System\Stdlib\Object;

/**
 *
 */
class Crusade extends ConfigObject
{
    /**
     * @struct self
     * 
     * @id       integer
     * @act      integer
     * @seq      integer
     * @caplvl   integer
     * @sumlvl   integer
     * @sucrto   integer
     * @duration integer
     * @times    integer
     * @exp      integer
     * @void     integer
     * @source   array( (integer)epid => (integer)prob )
     * @resource array( (integer)eid  => (integer)num )
     */

    /**
     * @param boolean $isMember
     * @return Object
     */
    public function getExp($isMember = false)
    {
        if ($isMember) {
            return $this->exp + ($this->exp >> 1);
        }
        return $this->exp;
    }

    /**
     * @return Object
     */
    public function getRewards()
    {
        $rewards = new Object;
        foreach ($this->resource as $eid => $cnt) {
            if (null === ($o = $rewards->get($eid))) {
                $e = Store::get('entity', $eid);
                if ($e) {
                    $rewards->set($eid, array(
                        'e' => $e,
                        'n' => $cnt,
                    ));
                }
            } else {
                $o->n += $cnt;
            }
        }

        $ep = new stdClass;
        $ep->pick = 1;
        $ep->void = $this->void;
        $ep->deep = 255;
        foreach ($this->source as $k => $v) {
            $ep->source[$k] = $v;
        }

        $ep = new EntityPicked($ep);
        foreach ($ep->pick() as $eid => $o) {
            if (null == ($x = $rewards->get($eid))) {
                $rewards->set($eid, $o);
            } else {
                $x->n += $o->n;
            }
        }

        return $rewards;
    }
}
