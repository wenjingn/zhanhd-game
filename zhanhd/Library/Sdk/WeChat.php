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
use Zhanhd\Library\Curl;

/**
 *
 */
class WeChat extends QQ
{
    /**
     * @var string
     */
    protected static $appId  = 'wx1253cc5dbfcd66fd';
    protected static $appKey = 'b59990565186f59c3026338e2ed0b18d';

    /**
     * @var string
     */
    protected static $verifyLogin  = '/auth/check_token';
    protected static $refreshToken = '/auth/refresh_token';

    /**
     * @var string
     */
    protected static $cookieSessionId   = 'hy_gameid';
    protected static $cookieSessionType = 'wc_actoken';

    /**
     * @param string $openid
     * @param string $accessToken
     * @param mixed &$ret
     * @return boolean
     */
    public function verifyLogin($openid, $accessToken, &$ret)
    {
        $ts = time();
        $res = $this->curl->setPath(static::$verifyLogin)->setQuery([
            'appid'     => static::$appId,
            'timestamp' => $ts,
            'sig'       => md5(static::$appKey.$ts),
            'encode'    => 1,
            'openid'    => $openid,
        ])->setPost([
            'openid'      => $openid,
            'accessToken' => $accessToken,
        ], 'json_encode')->request();

        if (false === $ret) {
            return false;
        }
        $ret = json_decode($res);
        if (null === $ret) {
            $ret = $res;
            return false;
        }
        return true;
    }

    /**
     * @param string $openid
     * @param string $refreshToken
     * @param mixed &$ret
     * @return boolean
     */
    public function refreshToken($openid, $refreshToken, &$ret)
    {
        $ts = time();
        $res = $this->curl->setPath(static::$refreshToken)->setQuery([
            'appid'     => static::$appId,
            'timestamp' => $ts,
            'sig'       => md5(static::$appKey.$ts),
            'encode'    => 1,
            'openid'    => $openid,
        ])->setPost([
            'appid'        => static::$appId,
            'refreshToken' => $refreshToken,
        ], 'json_encode')->request();

        if (false === $ret) {
            return false;
        }

        $ret = json_decode($res);
        if (null === $ret) {
            $ret = $res;
            return false;
        }
        return true;
    }
}
