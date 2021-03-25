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
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\Entity\Entity;

/**
 *
 */
class Equip extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('gid', new U32);
        $this->attach('pos', new U32);
        $this->attach('use', new U16);

        $this->attach('pe', new Entity);
    }
}
