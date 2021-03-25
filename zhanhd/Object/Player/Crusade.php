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
use System\Stdlib\Object,
    System\Stdlib\PhpPdo,
    System\Object\DatabaseObject;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Object\Player        as Owner,
    Zhanhd\Object\Player\Lineup as PlayerLineup;

/**
 *
 */
class Crusade extends DatabaseObject
{
    /**
     * @var SourceCrusade
     */
    public $crusade = null;

    /**
     * @var integer
     */
    const FLAG_ATTACKING = 1;
    const FLAG_RECALLING = 2;
    const FLAG_ACCEPTING = 3;
    const FLAG_DONE      = 4;

    /**
     *
     * @return integer
     */
    public function remaining()
    {
        return max(
            $this->crusade->duration - (integer) (($this->ustime - $this->updated + 999999) / 1000000),
            0
        );
    }

    /**
     *
     * @return void
     */
    public function autoStatus()
    {
        if ($this->flags == static::FLAG_DONE) {
            return;
        }

        switch ($this->flags) {
        case static::FLAG_ATTACKING:
            if ($this->remaining() == 0) {
                $this->flags = static::FLAG_ACCEPTING;
            }

            break;

        case static::FLAG_RECALLING:
        case static::FLAG_ACCEPTING:
        case static::FLAG_DONE:
            break;
        }
    }

    /**
     *
     * @param  Owner $o
     * @return mixed
     */
    public function getRewards(Owner $o)
    {
        $caplvl = 1;
        $sumlvl = $o->getLineup($this->gid)->lvlsum;
        $seed = $this->crusade->sucrto + 3 * ($caplvl - $this->crusade->caplvl) + ($sumlvl - $this->crusade->sumlvl);
        if ($seed <= 0 || ($seed < 100 && mt_rand(1, 100) > $seed)) {
            return;
        }

        return $this->crusade->getRewards();
    }

    /**
     *
     * @param  PhpPdo $pdo
     * @param  Owner  $o
     * @return Object
     */
    public static function gets(PhpPdo $pdo, Owner $o)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`PlayerCrusade` WHERE `pid` = ?', array(
            $o->id
        ), true);
    }

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerCrusade`';
    }

    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'pid'     => 0,
            'cid'     => 0,
            'gid'     => 0,
            'flags'   => 0,
            'times'   => 0,
            'created' => 0,
            'updated' => 0,
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
            'cid' => null,
        ];
    }

    /**
     *
     * @return void
     */
    protected function postSelect()
    {
        $this->crusade = Store::get('crusade', $this->cid);
    }
}
