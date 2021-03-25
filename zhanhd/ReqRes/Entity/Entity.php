<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Entity;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
class Entity extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('forge', new U32);
        $this->attach('peid',  new U64);
        $this->attach( 'eid',  new U32);
    }
}
