<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Library\Sdk;

/**
 *
 */
use Zhanhd\Library\Curl,
    Zhanhd\Object\Order,
    Zhanhd\Config\Store;

/**
 *
 */
class Lezhuo
{
    /**
     * @var static
     */
    public static $appid  = 'e10c91dc67c7';
    public static $appkey = '771710be9c0441b0a44978c5';

    /**
     * @var static
     */
    public static $sdkHost = 'sdk.lezhuogame.com';
    public static $apiHost = 'oapi.lezhuogame.com';

    /**
     * @return void
     */
    public function __construct()
    {
        $this->curl = new Curl;
    }

    /**
     * @param string $appvers
     * @param string $token
     * @param string $device
     * @param string $deviceuuid
     * @param string $mixcode
     * @param string $os
     * @param string $osvers
     * @param mixd   &$ret
     * @return boolean
     */
    public function getInfo($appvers, $token, $device, $deviceuuid, $mixcode, $os, $osvers, &$ret)
    {
        $fun  = 'GetInfo';
        $time = time();
        $sign = md5(self::$appid.$appvers.$device.$deviceuuid.$fun.$mixcode.$os.$osvers.$time.$token.self::$appkey);
        $res = $this->curl->setHost(self::$sdkHost)->setPath('/OAuth1/User/GetInfo')->setPost([
            'appid'      => self::$appid,
            'appvers'    => $appvers,
            'token'      => $token,
            'device'     => $device,
            'deviceuuid' => $deviceuuid,
            'fun'        => $fun,
            'mixcode'    => $mixcode,
            'os'         => $os,
            'osvers'     => $osvers,
            'time'       => $time,
            'sign'       => $sign,
        ])->request();

        if (false === $res) {
            return false;
        }

        $ret = json_decode($res);
        if ($ret === null) {
            $ret = $res;
            return false;
        }
        return true;
    }

    /**
     * @param User   $u
     * @param Player $p
     * @return boolean
     */
    public function postRoleCreate($u, $p, &$ret)
    {
        $time = (int)($u->created/1000000);
        $fun = 'PostRoleCreate';
        $pl = $p->getLineups()->get(1);
        $playerlevel = $pl->getLvlsum();
        $sign = md5(
            $u->login.
            self::$appid.
            $p->zone.
            $u->profile->cpscid.
            $u->profile->device.
            $u->profile->deviceuuid.
            $u->profile->from.
            $fun.
            $u->profile->ip.
            $u->profile->mixcode.
            $u->profile->os.
            $u->profile->osvers.
            $p->id.
            $playerlevel.
            $p->name.
            $time.
            self::$appkey
        );
        $res = $this->curl->setHost(self::$apiHost)->setPath('/OAuth1/Game/PostRoleCreate')->setPost([
            'account'    => $u->login,
            'appid'      => self::$appid,
            'area'       => $p->zone,
            'cpscid'     => $u->profile->cpscid,
            'device'     => $u->profile->device,
            'deviceuuid' => $u->profile->deviceuuid,
            'from'       => $u->profile->from,
            'fun'        => $fun,
            'mixcode'    => $u->profile->mixcode,
            'ip'         => $u->profile->ip,
            'os'         => $u->profile->os,
            'osvers'     => $u->profile->osvers,
            'playerid'   => $p->id,
            'playerlevel'=> $playerlevel,
            'role'       => $p->name,
            'time'       => $time,
            'sign'       => $sign,
        ])->request();

        if (false === $res) {
            return false;
        }
        $ret = json_decode($res);
        if ($ret === null) {
            $ret = $res;
            return false;
        }
        return true;
    }

    /**
     * @param User   $u
     * @param Player $p
     * @return boolean
     */
    public function postRoleLogon($u, $p, &$ret)
    {
        $time = (int)($p->lastLogin/1000000);
        $fun  = 'PostRoleLogon';
        $pl = $p->getLineups()->get(1);
        $playerlevel = $pl->getLvlsum();
        $sign = md5(
            $u->login.
            self::$appid.
            $p->zone.
            $u->profile->cpscid.
            $u->profile->device.
            $u->profile->deviceuuid.
            $u->profile->from.
            $fun.
            $u->profile->ip.
            $u->profile->mixcode.
            $u->profile->os.
            $u->profile->osvers.
            $p->id.
            $playerlevel.
            $p->name.
            $time.
            self::$appkey
        );
        $res = $this->curl->setHost(self::$apiHost)->setPath('/OAuth1/Game/PostRoleLogon')->setPost([
            'account'    => $u->login,
            'appid'      => self::$appid,
            'area'       => $p->zone,
            'cpscid'     => $u->profile->cpscid,
            'device'     => $u->profile->device,
            'deviceuuid' => $u->profile->deviceuuid,
            'from'       => $u->profile->from,
            'fun'        => $fun,
            'mixcode'    => $u->profile->mixcode,
            'ip'         => $u->profile->ip,
            'os'         => $u->profile->os,
            'osvers'     => $u->profile->osvers,
            'playerid'   => $p->id,
            'playerlevel'=> $playerlevel,
            'role'       => $p->name,
            'time'       => $time,
            'sign'       => $sign,
        ])->request();

        if (false === $res) {
            return false;
        }
        $ret = json_decode($res);
        if ($ret === null) {
            $ret = $res;
            return false;
        }
        return true;
    }

    /**
     * @param User   $u
     * @param Player $p
     * @param Order  $order
     * @return boolean
     */
    public function postPay($u, $p, $order, &$ret)
    {
        $merch = Store::get('merchandise', $order->merchandise);
        $fee = $merch->price;
        $ckey = $merch->getCounterKey();
        $amount = $merch->getDiamond(false);
        $prevamount = $p->gold-$merch->getDiamond($p->counter->$ckey == 1);
        $lastamount = $p->gold;
        
        $time = time();
        $fun = 'PostPay';
        $pl = $p->getLineups()->get(1);
        $playerlevel = $pl->getLvlsum();
        $sign = md5(
            $u->login.
            $amount.
            self::$appid.
            $p->zone.
            $u->profile->cpscid.
            $u->profile->device.
            $u->profile->deviceuuid.
            $fee.
            $u->profile->from.
            $fun.
            $u->profile->ip.
            $lastamount.
            $u->profile->mixcode.
            $u->profile->os.
            $u->profile->osvers.
            $p->id.
            $playerlevel.
            $prevamount.
            $time.
            $order->id.
            self::$appkey
        );
        $res = $this->curl->setHost(self::$apiHost)->setpath('/OAuth1/Game/PostPay')->setPost([
            'account'    => $u->login,
            'amount'     => $amount,
            'appid'      => self::$appid,
            'area'       => $p->zone,
            'cpscid'     => $u->profile->cpscid,
            'device'     => $u->profile->device,
            'deviceuuid' => $u->profile->deviceuuid,
            'fee'        => $fee,
            'from'       => $u->profile->from,
            'fun'        => $fun,
            'mixcode'    => $u->profile->mixcode,
            'ip'         => $u->profile->ip,
            'lastamount' => $lastamount,
            'os'         => $u->profile->os,
            'osvers'     => $u->profile->osvers,
            'playerid'   => $p->id,
            'playerlevel'=> $playerlevel,
            'prevamount' => $prevamount,
            'time'       => $time,
            'transid'    => $order->id,
            'sign'       => $sign,
        ])->request();

        if (false === $res) {
            return false;
        }
        $ret = json_encode($res);
        if ($ret === null) {
            $ret = $res;
            return false;
        }
        return true;
    }
}
