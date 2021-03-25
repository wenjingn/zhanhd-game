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
class QQ
{
    /**
     * @var string
     */
    //protected static $server = 'msdktest.qq.com';
    protected static $server = 'msdk.qq.com';
    protected static $appId  = '1105010813';
    protected static $appKey = 'e7K2qOEZKFBghmUL';

    protected static $payAppId  = '1105010813';
    //protected static $payAppKey = 'e7K2qOEZKFBghmUL';
    protected static $payAppKey = 'ff9RQGLlPDg9aUHRhIGejpR7IQNeNGCK';
    
    /**
     * @var string
     */
    protected static $verifyLogin = '/auth/verify_login';
    
    /**
     * @var string
     */
    protected static $cookieSessionId   = 'openid';
    protected static $cookieSessionType = 'kp_actoken';

    /**
     * @var array
     */
    protected static $interfaces = [
        'balanceQuery' => '/mpay/get_balance_m',
        'pay'          => '/mpay/pay_m',
        'payCancel'    => '/mpay/cancel_pay_m',
    ];

    /**
     * @return string
     */
    public static function getAppId()
    {
        return static::$appId;
    }

    /**
     * @return string
     */
    public static function getPayAppId()
    {
        return static::$payAppId;
    }

    /**
     * @return string
     */

    /**
     * @param string $method
     * @param string $path
     * @param array  $params
     * @param string $secret
     * @return string
     */
    public static function makeSig($method, $path, $params, $secret)
    {
        ksort($params);
        $params_string = [];
        foreach ($params as $k => $v) {
            $params_string[] = $k.'='.$v;
        }
        $params_string = implode('&', $params_string);
        $data = sprintf('%s&%s&%s', strtoupper($method), rawurlencode($path), rawurlencode($params_string));
        return base64_encode(hash_hmac('sha1', $data, $secret.'&', true));
    }

    /**
     * @return void
     */
    public function __construct()
    {
        $this->curl = new Curl;
        $this->curl->setHost(static::$server);
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->curl->close();
    }

    /**
     * @return integer
     */
    public function getErrno()
    {
        return $this->curl->getErrno();
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->curl->getError();
    }

    /**
     * @param string $openid
     * @param string $openkey
     * @param mixed &$ret
     * @return boolean
     */
    public function verifyLogin($openid, $openkey, &$ret)
    {
        $ts = time();
        $res = $this->curl->setPath(static::$verifyLogin)->setQuery([
            'appid'     => static::$appId,
            'timestamp' => $ts,
            'sig'       => md5(static::$appKey.$ts),
            'encode'    => 1,
            'openid'    => $openid,
        ])->setPost([
            'appid'   => static::$appId,
            'openid'  => $openid,
            'openkey' => $openkey,
        ], 'json_encode')->request();

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
     * @param string $openid
     * @param string $openkey
     * @param string $pay_token
     * @param string $pf
     * @param string $pfkey
     * @param integer $zoneid
     * @param mixed &$ret
     * @return boolean
     */
    public function balanceQuery($openid, $openkey, $pay_token, $pf, $pfkey, $zoneid, &$ret)
    {
        $query = [
            'openid'    => $openid,
            'openkey'   => $openkey,
            'pay_token' => $pay_token,
            'appid'     => static::$payAppId,
            'ts'        => time(),
            'pf'        => $pf, 
            'pfkey'     => $pfkey,
            'zoneid'    => $zoneid,
            'format'    => 'json',
        ];
        $sig = static::makeSig('GET', static::$interfaces['balanceQuery'], $query, static::$payAppKey);
        $query['sig'] = $sig;
        $res = $this->curl->setPath(static::$interfaces['balanceQuery'])->setQuery($query)->setCookie([
            'session_id'   => static::$cookieSessionId,
            'session_type' => static::$cookieSessionType,
            'org_loc'      => static::$interfaces['balanceQuery'],
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
     * @param string $openid
     * @param string $openkey
     * @param string $pay_token
     * @param string $pf
     * @param string $pfkey
     * @param integer $zoneid
     * @param integer $amt
     * @param mixed &$ret
     * @return boolean
     */
    public function pay($openid, $openkey, $pay_token, $pf, $pfkey, $zoneid, $amt, &$ret)
    {
        $query = [
            'openid'    => $openid,
            'openkey'   => $openkey,
            'pay_token' => $pay_token,
            'appid'     => static::$payAppId,
            'ts'        => time(),
            'pf'        => $pf,
            'pfkey'     => $pfkey,
            'zoneid'    => $zoneid,
            'format'    => 'json',
            'amt'       => $amt,
        ];
        $sig = static::makeSig('GET', static::$interfaces['pay'], $query, static::$payAppKey);
        $query['sig'] = $sig;
        $res = $this->curl->setPath(static::$interfaces['pay'])->setQuery($query)->setCookie([
            'session_id'   => static::$cookieSessionId,
            'session_type' => static::$cookieSessionType,
            'org_loc'      => static::$interfaces['pay'],
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
     * @param string $openid
     * @param string $openkey
     * @param string $pay_token
     * @param string $pf
     * @param string $pfkey
     * @param integer $zoneid
     * @param integer $amt
     * @param string $billno
     * @param mixed &$ret
     * @return boolean
     */
    public function payCancel($openid, $openkey, $pay_token, $pf, $pfkey, $zoneid, $amt, $billno, &$ret)
    {
        $query = [
            'openid'    => $openid,
            'openkey'   => $openkey,
            'pay_token' => $pay_token,
            'appid'     => static::$payAppId,
            'ts'        => time(),
            'pf'        => $pf,
            'pfkey'     => $pfkey,
            'zoneid'    => $zoneid,
            'format'    => 'json',
            'amt'       => $amt,
            'billno'    => $billno,
        ];
        $sig = static::makeSig('GET', static::$interfaces['payCancel'], $query, static::$payAppKey);
        $query['sig'] = $sig;

        $res = $this->curl->setPath(static::$interfaces['payCancel'])->setQuery($query)->setCookie([
            'session_id'   => static::$cookieSessionId,
            'session_type' => static::$cookieSessionType,
            'org_loc'      => static::$interfaces['payCancel'],
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
}
