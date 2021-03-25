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
class Baidu
{
    /**
     * @var string
     */
    private static $server1   = 'querysdkapi.baidu.com';
    private static $server2   = 'querysdkapi.91.com';
    private static $appId     = '7673590';
    private static $secretKey = 'ecd0vERw9G38UXv8GZDqbQVU3sqdxs4C';

    /**
     * @var array
     */
    private static $interfaces = [
        'verifyLogin' => '/query/cploginstatequery',
        'orderQuery'  => '/CpOrderQuery.ashx',
    ];

    /**
     * @return integer
     */
    public static function getAppId()
    {
        return self::$appId;
    }

    /**
     * @param string $resultCode
     * @param string $content
     * @return string
     */
    public static function signMd5($resultCode, $content = '')
    {
        return md5(self::$appId.$resultCode.$content.self::$secretKey);
    }

    /**
     * @return void
     */
    public function __construct()
    {
        $this->curl = new Curl;
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
     * @param string $accessToken
     * @param array|string &$ret
     * @return boolean
     */
    public function verifyLogin($accessToken, &$ret)
    {
        $sign = md5(self::$appId.$accessToken.self::$secretKey);
        $res = $this->curl->setHost(self::$server1)->setPath(self::$interfaces['verifyLogin'])->setPost([
            'AppID' => self::$appId,
            'AccessToken' => $accessToken,
            'Sign' => $sign,
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
     * @param string $orderSerial
     * @param array|string &$ret
     * @return boolean
     */
    public function orderQuery($orderSerial, &$ret)
    {
        $sign = md5(self::$appId.$orderSerial.self::$secretKey);
        $res = $this->curl->setHost(self::$server2)->setPath(self::$interfaces['orderQuery'])->setPost([
            'AppID'  => self::$appId,
            'Action' => 10002,
            'CooperatorOrderSerial' => $orderSerial,
            'Sign' => $sign,
            'OrderType' => 1,
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
     * @const integer
     */
    const FLAG_SUCCESS     = 0;
    const FLAG_APPID_ERROR = 1;
    const FLAG_SIGN_ERROR  = 2;

    /**
     * @param integer $appId
     * @param string $baiduSerial
     * @param string $serial
     * @param string $sign
     * @param string $content
     * @return object
     */
    public static function orderListen($appId, $baiduSerial, $serial, $sign, $content)
    {
        if ($appId != self::$appId) {
            return self::FLAG_APPID_ERROR;
        }

        if ($sign != md5(self::$appId.$baiduSerial.$serial.$content.self::$secretKey)) {
            return self::FLAG_SIGN_ERROR;
        }

        return self::FLAG_SUCCESS;
    }
}
