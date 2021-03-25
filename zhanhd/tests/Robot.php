<?php
/**
 * $Id$
 */

/**
 *
 */
$dir = getlongopt(['appdir' => '/data/php/games/zhanhd']);
(new PsrAutoloader)->register('System', '/data/php/games/system');
(new PsrAutoloader)->register('Zhanhd', $dir['appdir']);

/**
 *
 */
use System\ReqRes\ReqResInterface,
    System\Stdlib\Object,
    System\Swoole\Client,
    System\Swoole\ReqResHeader,
    System\ReqRes\Int\U16;

/**
 *
 */
use Zhanhd\ReqRes\ErrorResponse,
    Zhanhd\ReqRes\Account\Signin\Request         as SigninRequest,
    Zhanhd\ReqRes\Platform\Baidu\Login\Request   as BaiduLoginRequest,
    Zhanhd\ReqRes\Platform\Tencent\Login\Request as TencentLoginRequest,
    Zhanhd\ReqRes\Platform\AY\Login\Request      as AYLoginRequest,
    Zhanhd\ReqRes\Platform\Lezhuo\Login\Request  as LezhuoLoginRequest,
    Zhanhd\ReqRes\Account\InitResponse,
    Zhanhd\Object\User;

/**
 *
 */
class Robot
{
    /**
     * @var Object
     */
    private $swEvents = null;

    /**
     * @var swoole_client
     */
    private $swClient = null;

    /**
     * @var Client
     */
    private $client = null;

    /**
     * @param integer $platform
     * @param boolean $login
     * @return void
     */
    public function __construct($login = true)
    {
        $this->client = new Client;

        $this->swEvents = new Object;
        $this->swClient = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

        $this->swClient->on('error', function() {});
        $this->swClient->on('close', function() {});

        $this->swClient->on('connect', function($c) {
            $this->proc('connect');
        });

        $this->swClient->on('receive', function($c, $q) {
            $this->client->query->concat($q);

            while ($this->client->query->strlen()) {
                if (($retval = $this->client->processInputBuffer(0)) == -1) {
                    $this->logmessage('protocol error');
                    $c->close();
                    return;
                } else if ($retval) {
                    break;
                }

                $this->proc($this->client->qhead->command->intval(), $this->client->qargv);
                $this->client->reset();
            }
        });

        $platform = getlongopt([ 'platform' => User::PF_ZHANHD ])['platform'];
        if ($login) {
            switch ($platform) {
            case User::PF_ZHANHD:
                $params = (new Object)->import(getlongopt([
                    'username' => false,
                    'password' => false,
                    'zone'     => false,
                ]));

                $this->on('connect', function($c, $q) use ($params) {
                    $r = new SigninRequest;
                    $r->login->strval($params->username);
                    $r->passwd->strval($params->password);
                    $r->zone->intval($params->zone);
                    $c->send(self::encodeWithHeader(4, $r));
                });
                break;
            case User::PF_BAIDU:
                $params = (new Object)->import(getlongopt([
                    'uid' => false,
                    'accessToken' => false,
                    'zone' => false,
                ]));
                
                $this->on('connect', function($c, $q) use ($params) {
                    $r = new BaiduLoginRequest;
                    $r->uid->strval($params->uid);
                    $r->accessToken->strval($params->accessToken);
                    $r->zone->intval($params->zone);
                    $c->send(self::encodeWithHeader(103, $r));
                });
                break;
            case User::PF_QQ:
            case User::PF_WECHAT:
                $params = (new Object)->import(getlongopt([
                    'zone'        => false,
                    'openid'      => false,
                    'accessToken' => false,
                    'pf'          => false,
                    'pfkey'       => false,
                    'anotherToken'=> false,
                ]));

                $this->on('connect', function($c, $q) use ($params, $platform) {
                    $r = new TencentLoginRequest;
                    $r->zone->intval($params->zone);
                    $r->platform->intval($platform);
                    $r->openid->strval($params->openid);
                    $r->accessToken->strval($params->accessToken);
                    $r->pf->strval($params->pf);
                    $r->pfkey->strval($params->pfkey);
                    $r->anotherToken->strval($params->anotherToken);
                    $c->send(self::encodeWithHeader(115, $r));
                });
                break;
            case User::PF_AY:
                $params = (new Object)->import(getlongopt([
                    'zone'      => false,
                    'accountid' => false,
                    'sessionid' => false,
                ]));

                $this->on('connect', function($c, $q) use ($params, $platform) {
                    $r = new AYLoginRequest;
                    $r->accountid->strval($params->accountid);
                    $r->sessionid->strval($params->sessionid);
                    $r->zone->intval($params->zone);
                    $c->send(self::encodeWithHeader(31, $r));
                });
                break;
            case User::PF_LEZHUO:
                $params = (new Object)->import(getlongopt([
                    'zone'       => false,
                    'appvers'    => false,
                    'token'      => false,
                    'device'     => false,
                    'deviceuuid' => false,
                    'mixcode'    => '',
                    'os'         => false,
                    'osvers'     => false,
                    'from'       => false,
                    'cpscid'     => '',
                ]));

                $this->on('connect', function($c, $q) use ($params) {
                    $r = new LezhuoLoginRequest;
                    $r->zone->intval($params->zone);
                    $r->appvers->strval($params->appvers);
                    $r->token->strval($params->token);
                    $r->device->strval($params->device);
                    $r->deviceuuid->strval($params->deviceuuid);
                    $r->mixcode->strval($params->mixcode);
                    $r->os->strval($params->os);
                    $r->osvers->strval($params->osvers);
                    $r->from->strval($params->from);
                    $r->cpscid->strval($params->cpscid);
                    $c->send(self::encodeWithHeader(229, $r));
                });
                break;
            }
        }

        $this->on(1, function($c, $q) {
            print_r((new ErrorResponse)->decode($q));
            //$c->close();
        });
    }

    /**
     *
     * @return void
     */
    public function on($cmd, callable $callback)
    {
        $this->swEvents->get($cmd, array())->set(null, $callback);
    }

    /**
     *
     * @param  string  $host
     * @param  integer $port
     * @return void
     */
    public function connect($host, $port)
    {
        $this->swClient->connect($host, $port);
    }

    /**
     * @param integer $cmd
     * @param ReqResInterface $req
     * @return string
     */
    public static function encodeWithHeader($cmd, ReqResInterface $req = null)
    {
        $bulklen = new U16;
        $command = new U16;
        $command->intval($cmd);
        
        if ($req) {
            $bulklen->intval(4 + $req->length());
            return $bulklen->encode().$command->encode().$req->encode();
        }

        $bulklen->intval(4);
        return $bulklen->encode().$command->encode();
    }

    /**
     *
     * @param  integer $cmd
     * @param  string  $qargv
     * @return void
     */
    private function proc($cmd, $qargv = null)
    {
        $qargv = $this->client->qhead->bulklen->encode() . $this->client->qhead->command->encode() . $qargv;
        foreach ($this->swEvents->get($cmd, array()) as $callback) {
            $callback($this->swClient, $qargv);
        }
    }

    /**
     *
     * @return void
     */
    private function logmessage($fmt, ... $argv)
    {
        vprintf($fmt, $argv);
    }
}
