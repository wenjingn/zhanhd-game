<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Platform\Tencent;

/**
 *
 */
use Zhanhd\ReqRes\Platform\Tencent\BalanceResponse,
    Zhanhd\Object\User,
    Zhanhd\Library\Sdk\QQ,
    Zhanhd\Library\Sdk\WeChat;

/**
 *
 */
class Module
{
    /**
     * @return void
     */
    public static function aspect($c, $u, $global)
    {
        /* query tencent sdk */
        if ($u->platform == User::PF_QQ) {
            $sdk = new QQ;
        } else if ($u->platform == User::PF_WECHAT) {
            $sdk = new WeChat;
        }

        if (false === $sdk->balanceQuery($u->login, $u->passwd, $u->profile->payToken, $u->profile->pf, $u->profile->pfkey, $c->local->player->zone, $ret)) {
            return $c->addReply($global->errorResponse->error('sdk communication failure'));
        }

        if ($ret->ret != 0) {
            return $c->addReply($global->errorResponse->error('sdk authentication failure'));
        }
        $r = new BalanceResponse;
        $r->balance->intval($ret->balance);
        $c->addReply($r);
    }
}
