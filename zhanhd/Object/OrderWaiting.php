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
use System\Object\DatabaseObject;

/**
 *
 */
class OrderWaiting extends DatabaseObject
{
    /**
     * @const integer
     */
    const FLAG_INIT = 0;
    const FLAG_DONE = 1;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`OrderWaiting`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'id'      => 0,
            'uniqrec' => '',
            'pid'     => 0,
            'receipt' => '',
            'flag'    => 0,
        ];
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
     * @return void
     */
    protected function postInsert()
    {
        $this->id = $this->phppdo->lastInsertId();
    }
    
    /**
     * @param string $uniqrec
     * @return boolean
     */
    public function findByUniqrec($uniqrec)
    {
        return $this->findBySql('SELECT * FROM `zhanhd.player`.`OrderWaiting` WHERE `uniqrec` = ? limit 1', [$uniqrec]);
    }

    /**
     * @param PhpPdo $pdo
     * @return integer
     */
    public static function getTotal($pdo)
    {
        $stmt = $pdo->prepare('SELECT COUNT(1) FROM `zhanhd.player`.`OrderWaiting` WHERE `flag`=?');
        $stmt->execute([self::FLAG_INIT]);
        return (integer)$stmt->fetchColumn(0);
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $start
     * @param integer $length
     * @return Object
     */
    public static function getPage($pdo, $start, $length)
    {
        return self::buildSet(
            $pdo,
            __CLASS__,
            sprintf('SELECT * FROM `zhanhd.player`.`OrderWaiting` WHERE `flag`=? LIMIT %d,%d', $start, $length),
            [self::FLAG_INIT]
        );
    }
}
