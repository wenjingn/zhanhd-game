<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player;

/**
 *
 */
use System\Stdlib\PhpPdo,
    System\Object\DatabaseObject;

/**
 *
 */
use Zhanhd\Object\Player as Owner,
    Zhanhd\Config\Store;

/**
 *
 */
class Building extends DatabaseObject
{
    /**
     * @var integer
     */
    const FLAG_UPGRADING = 1;

    /**
     * @var SourceBuilding
     */
    public $b = null;

    /**
     * @var object
     */
    public $cbp = null,
           $nbp = null;

    /**
     *
     * @param  boolean $upgrade
     * @return array
     */
    public function collect($upgrade = false)
    {
        $es = [];
        if ($this->b->collectable && $this->cbp->ctmin && $this->cbp->ctmax) {
            // how many 'unit time' past
            $utp = min($this->cbp->ctmax, $this->elapsedTime($this->cltts, true)) / $this->cbp->ctmin;

            foreach ($this->cbp->productions as $eid => $num) {
                if ($upgrade) {
                    $num = floor($utp  * $num);
                } else {
                    $num = floor($utp) * $num;
                }

                if ($num > 0) {
                    $es[$eid] = $num;
                }
            }
        }

        if (count($es) > 0) {
            $this->cltts = $this->ustime;
        }

        return $es;
    }

    /**
     *
     * @return void
     */
    public function upgrade()
    {
        $this->flags |= self::FLAG_UPGRADING;
        $this->lvl ++;
        $this->ugdts  = $this->ustime;

        $this->cbp = $this->nbp;
        $this->nbp = array_key_exists($this->lvl + 1, $this->b->level) ? $this->b->level[$this->lvl + 1] : null;
    }

    /**
     *
     * @return integer
     */
    public function getUpgradeRemainTime()
    {
        if ($this->flags & self::FLAG_UPGRADING) {
            return $this->cbp->ugdur - $this->elapsedTime($this->ugdts, true);
        }

        return -1;
    }

    /**
     *
     * @return integer
     */
    public function getFullRemainTime()
    {
        if ($this->b->collectable) {
            return $this->cbp->ctmax - $this->elapsedTime($this->cltts, true);
        }

        return -1;
    }

    /**
     *
     * @param  PhpPdo  $pdo
     * @param  integer $pid
     * @return Object
     */
    public static function gets(PhpPdo $pdo, Owner $o)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`PlayerBuilding` WHERE `pid` = ?', array(
            $o->id
        ), true);
    }

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerBuilding`';
    }

    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'pid'   => 0,
            'bid'   => 0,
            'lvl'   => 0,
            'flags' => 0,
            'cltts' => 0,
            'ugdts' => 0,
        ];
    }

    /**
     *
     * @return array
     */
    public function primary()
    {
        return [
            'pid' => null,
            'bid' => null,
        ];
    }

    /**
     *
     * @return void
     */
    protected function postSelect()
    {
        $this->b   = Store::get('building', $this->bid);
        
        if (false === isset($this->b->level)) {
            return;
        }

        $this->cbp = $this->b->level[$this->lvl];
        $this->nbp = array_key_exists($this->lvl + 1, $this->b->level) ? $this->b->level[$this->lvl + 1] : null;
    }
}
