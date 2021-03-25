<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object;

/**
 *
 */
use System\Stdlib\PhpPdo,
    System\Stdlib\Object,
    System\Object\DatabaseObject;

/**
 *
 */
class Order extends DatabaseObject
{
    /**
     * @const integer
     */
    const STATUS_INITIAL = 1;
    const STATUS_WAITING = 2;
    const STATUS_READYON = 3;
    const STATUS_SUCCESS = 4;
    const STATUS_FAILURE = 5;
    const STATUS_CLOSED  = 6;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`Order`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'id'          => 0,
            'serial'      => '',
            'pid'         => 0,
            'merchandise' => 0,
            'created'     => 0,
            'updated'     => 0,
            'status'      => 0,
        ];
    }

    /**
     * @return boolean
     */
    public function findBySerial($serial)
    {
        return $this->findBySql('SELECT * FROM `zhanhd.player`.`Order` WHERE `serial` = ? limit 1', [$serial]);
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'id' => null,
        ];
    }

    /**
     * @param PhpPdo  $pdo
     * @param integer $pid
     * @return Object
     */
    public static function getsByPid(PhpPdo $pdo, $pid)
    {
        return self::buildSet(
            $pdo,
            __CLASS__,
            'SELECT * FROM `zhanhd.player`.`Order` WHERE `pid`=? AND `STATUS` = ?',
            [ $pid, self::STATUS_READYON ]
        );
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $start
     * @param integer $length
     * @return Object
     */
    public static function getPage(PhpPdo $pdo, $start, $length)
    {
        return self::buildSet(
            $pdo, 
            __CLASS__, 
            sprintf('SELECT * FROM `zhanhd.player`.`Order` WHERE `STATUS` < ? LIMIT %d,%d', $start, $length), 
            [self::STATUS_READYON]
        );
    }

    /**
     * @param PhpPdo $pdo
     * @return integer
     */
    public static function getTotal(PhpPdo $pdo)
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM `zhanhd.player`.`Order` WHERE `STATUS` < ?');
        $stmt->execute([self::STATUS_READYON]);
        return (integer)$stmt->fetchColumn(0);
    }

    /**
     * @param integer $zone
     * @return string
     */
    public static function generate($ustime, $zone)
    {

        return sprintf('%03X%d%06d%04d', $zone, date('YmdGis', $ustime / 1000000), $ustime % 1000000, mt_rand(0, 9999));
    }

    /**
     * @param string $serial
     * @return integer
     */
    public static function getZone($serial)
    {
        return hexdec(substr($serial, 0, 3));
    }

    /**
     * @param string $serial
     * @return array
     */
    public static function parseAyOrder($serial)
    {
        list($ms, $merch, $zone) = explode('|', $serial);
        return [
            'ms' => $ms,
            'merch' => $merch,
            'zone' => $zone,
        ];
    }

    /**
     * @return void
     */
    protected function preInsert()
    {
        $this->created = $this->ustime;
    }

    /**
     * @return void
     */
    protected function postInsert()
    {
        $this->id = $this->phppdo->lastInsertId();
    }

    /**
     * @return void
     */
    protected function preUpdate()
    {
        $this->updated = $this->ustime;
    }
}
