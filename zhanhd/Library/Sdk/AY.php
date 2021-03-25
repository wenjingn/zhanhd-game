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
class AY
{
    /**
     * @var string
     */
    private static $appId = '100400';
    private static $appKey = 'be85d650a7af262e';
    private static $server = 'asdk.ay99.net';
    private static $port  = 8081;
    
    /**
     * @var array
     */
    private static $interfaces = [
        'verifyLogin' => '/loginvalid.php',
    ];

    /**
     * @param 
     * @return void
     */
    public function __construct()
    {
        $this->curl = new Curl;
    }

    /**
     * @param array $params
     * @param string $secret
     * @return string
     */
    public static function makeSig($params, $secret)
    {
        ksort($params);
        $param_str = [];
        foreach ($params as $k => $v) {
            $param_str[] = $k.'='.$v;
        }
        $param_str = implode('&', $param_str);
        $param_str.= $secret;
        return md5($param_str);
    }

    /**
     * @param string $accountId
     * @param string $sessionId
     * @param array &$ret
     * @return boolean
     */
    public function verifyLogin($accountId, $sessionId, &$ret)
    {
        $params = [
            'gameid' => self::$appId,
            'accountid' => $accountId,
            'sessionid' => $sessionId,
        ];

        $sign = self::makeSig($params, self::$appKey);
        $params['sign'] = $sign;
        $res = $this->curl->setHost(self::$server)->setPort(self::$port)->setPath(self::$interfaces['verifyLogin'])->setPost($params)->request();
        if (false === $res) {
            return false;
        }

        $ret = json_decode($res);
        if (null === $ret) {
            return false;
        }
        return true;
    }

    /**
     * @const integer
     */
    const FLAG_SUCCESS    = 0;
    const FLAG_SIGN_ERROR = 2;

    /**
     * @param string  $account
     * @param string  $money
     * @param integer $addtime
     * @param integer $orderid
     * @param string  $customorderid
     * @param string  $paytype
     * @param integer $senddate
     * @param string  $custominfo
     * @param integer $success
     * @param integer $sign
     */
    public static function orderListen($account, $money, $addtime, $orderid, $customorderid, $paytype, $senddate, $custominfo, $success, $sign)
    {
        $params = [
            'account' => $account,
            'money'   => $money,
            'addtime' => $addtime,
            'orderid' => $orderid,
            'customorderid' => $customorderid,
            'paytype' => $paytype,
            'senddate' => $senddate,
            'custominfo' => $custominfo,
            'success' => $success,
        ];
        if ($sign != self::makeSig($params, self::$appKey)) {
            return self::FLAG_SIGN_ERROR;
        }

        return self::FLAG_SUCCESS;
    }
}
