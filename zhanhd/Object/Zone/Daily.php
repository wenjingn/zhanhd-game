<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Zone;

/**
 *
 */
use System\Object\DatabaseObject;

/**
 *
 */
class Daily extends DatabaseObject
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`ZoneDaily`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'date' => 0,
            'k'    => '',
            'v'    => '',
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'date' => null,
            'k'    => null,
        ];
    }
    
    /**
     * @param integer $onlinePeak
     */
    public function updateOnlinePeak($onlinePeak)
    {
        $stmt = $this->phppdo->prepare('insert into `zhanhd.player`.`ZoneDaily` values (?, ?, ?) on duplicate key update v=?');
        $stmt->execute([
            $this->date, $this->k, $onlinePeak, $onlinePeak,
        ]);
    }
}
