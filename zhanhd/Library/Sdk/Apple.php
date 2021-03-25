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
class Apple
{
    /**
     * @var string
     */
    //private static $server = 'sandbox.itunes.apple.com';
    private static $server = 'buy.itunes.apple.com';

    /**
     * @var array
     */
    private static $interfaces = [
        'orderQuery' => '/verifyReceipt',    
    ];

    /**
     * @return void
     */
    public function __construct()
    {
        $this->curl = new Curl;
    }

    /**
     * error code:
     * 21000 App Store不能读取你提供的JSON对象
     * 21002 receipt-data域的数据有问题
     * 21003 receipt无法通过验证
     * 21004 提供的shared secret不匹配你账号中的shared secret
     * 21005 receipt服务器当前不可用
     * 21006 receipt合法，但是订阅已过期。服务器接收到这个状态码时，receipt数据仍然会解码并一起发送
     * 21007 receipt是Sandbox receipt，但却发送至生产系统的验证服务
     * 21008 receipt是生产receipt，但却发送至Sandbox环境的验证服务
     */

    /**
     * @param string $orderSerial
     * @param array|string &$ret
     * @return boolean
     */
    public function orderQuery($orderSerial, &$ret)
    {
        $res = $this->curl->setProtocol('https')->setHost(self::$server)->setPath(self::$interfaces['orderQuery'])->setPost([
            'receipt-data' => $orderSerial,        
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
     * @param object $receipt
     * @return integer
     */
    public static function getMerchId($receipt)
    {
        $parts = explode('.', $receipt->receipt->product_id);
        return (int)$parts[count($parts)-1];
    }
}
