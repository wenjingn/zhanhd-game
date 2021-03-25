<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Lineup;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\Entity\Entity;

/**
 *
 */
class EquipAuto extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('gid', new U32);
        $this->attach('pos', new U32);
    }
}
