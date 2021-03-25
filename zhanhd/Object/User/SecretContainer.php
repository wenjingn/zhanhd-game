<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\User;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Str,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
class SecretContainer extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('ll', new U64);
        $this->attach('ph', new Str);
        $this->attach('id', new U32);
        $this->attach('ih', new Str);
        $this->attach('ht', new Str);
    }
}
