<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Account;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Str,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\Object\User;

/**
 *
 */
class UserInfo extends Box
{
    /**
     *
     * @return void
     * @todo   setup flags
     */
    public function fromUserObject(User $u)
    {
        $this->login ->strval($u->login);
        $this->secret->strval($u->createSecret());
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('id',       new U64);
        $this->attach('flags',    new U16);
        $this->attach('login',    new Str);
        $this->attach('password', new Str);
        $this->attach('secret',   new Str);
    }
}
