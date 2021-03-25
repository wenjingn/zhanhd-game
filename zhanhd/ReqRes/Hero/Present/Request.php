<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Hero\Present;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Set,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\ReqRes\Entity\Prop;

/**
 *
 */
class Request extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('peid', new U64);
        $this->attach('props', new Set(new Prop));
    }
}
