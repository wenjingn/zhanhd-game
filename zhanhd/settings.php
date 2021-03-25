<?php
/**
 * $Id$
 */

/**
 *
 */
(new PsrAutoloader)->register('Zhanhd', $this->svConfig->appdir);

/**
 *
 */
use System\Stdlib\Object,
    System\Stdlib\PhpPdo,
    System\Swoole\Client,
    System\Swoole\Task,
    System\Swoole\Pipe,
    System\ReqRes\ReqResInterface,
    System\Object\DatabaseObject,
    System\Object\DatabaseProfile;

/**
 *
 */
use Zhanhd\ReqRes\ErrorResponse,
    Zhanhd\ReqRes\ClientBinding,
    Zhanhd\ReqRes\Achievement\UpdateResponse as AchievementUpdateResponse,
    Zhanhd\ReqRes\NewzoneMission\UpdateResponse as NewzoneMissionUpdateResponse,
    Zhanhd\ReqRes\WeekMission\UpdateResponse as WeekMissionUpdateResponse,
    Zhanhd\Object\User,
    Zhanhd\Object\Player,
    Zhanhd\Object\Zone,
    Zhanhd\Extension\Rank\Module        as RankModule,
    Zhanhd\Extension\PvpRank\Module     as PvpRankingModule,
    Zhanhd\Extension\Achievement\Module as AchievementModule,
    Zhanhd\Extension\NewzoneMission\Module as NewzoneMissionModule,
    Zhanhd\Extension\WeekMission\Module    as WeekMissionModule,
    Zhanhd\Extension\BadWordFilter,
    Zhanhd\Extension\Logout\Module         as LogoutModule,
    Zhanhd\Task\UpdateGuildRank,
    Zhanhd\Config\Store;

/**
 *
 */
return [
    /**
     * commands filepath
     */
    'commands' => $this->svConfig->appdir . '/commands',
    'tasks'    => $this->svConfig->appdir . '/tasks',
    'pipes'    => $this->svConfig->appdir . '/coherence',
    'cfgcache' => $this->svConfig->appdir . '/cache/config.data',
    'badwords' => $this->svConfig->appdir . '/cache/badwords.data.php',

    /**
     * application initialize callback
     */
    'initial' => function() {
        /* setup cache */
        Store::setup(unserialize(file_get_contents($this->appcfgs->cfgcache)));

        /* register resources */
        DatabaseObject  ::registerResource($this->globals, 'globals');
        DatabaseProfile ::registerResource($this->globals, 'globals');
        RankModule::registerResource($this->globals, 'globals');
        PvpRankingModule::registerResource($this->globals, 'globals');
        BadWordFilter::setBadwords(include $this->appcfgs->badwords);

        /* setup error-response object */
        $this->globals->errorResponse = new ErrorResponse;

        if (isset($this->svConfig->rundir)) {
            $o = (new Object)->import(include $this->svConfig->rundir.'/conf/zhanhd.config.php');
            $this->globals->pdo = new PhpPdo(sprintf('mysql:host=%s;port=%d;charset=utf8', $o->pdo->host, $o->pdo->port), $o->pdo->user, $o->pdo->pass);
            $this->globals->pdo->setAttribute(
                PhpPdo::ATTR_ERRMODE, PhpPdo::ERRMODE_EXCEPTION
            );
            $this->globals->redis = new Redis;
            $this->globals->redis->connect($o->redis->host, $o->redis->port, $o->redis->timeout, null, $o->redis->retry);
        } else {
            $o = $this->appcfgs->PhpIniParser('pdo.dsn.zhanhd', 'username', 'password');
            $this->globals->pdo = new PhpPdo($o->cfg, $o->username, $o->password);
            $this->globals->pdo->setAttribute(
                PhpPdo::ATTR_ERRMODE, PhpPdo::ERRMODE_EXCEPTION
            );

            /* setup redis instance */
            $o = $this->appcfgs->PhpIniParser('rds.cnt.zhanhd', 'host', 'port', 'timeout', 'retry');
            $this->globals->redis = new Redis;
            $this->globals->redis->connect($o->host, $o->port, $o->timeout, null, $o->retry);
        }

        /* parse-parameter helper */
        $this->globals->parseParameters = function(Client $c, ReqResInterface $request, $closeClientOnError = true) {
            try {
                $request->decode($c->qargv);
                $this->globals->debug($c, 'barg=%s', json_encode($request->export()));
                return $request;
            } catch (Exception $e) {
                if ($closeClientOnError) {
                    $c->flags->bitset(Client::FLAG_CLOSING);
                }

                $c->addReply($this->globals->errorResponse->error('invalid request arguments'));
                return -1;
            }
        };

        /* parse-parameter-none helper */
        $this->globals->parseParametersNone = function(Client $c, $closeClientOnError = true) {
            if ($c->qargv) {
                if ($closeClientOnError) {
                    $c->flags->bitset(Client::FLAG_CLOSING);
                }

                $c->addReply($this->globals->errorResponse->error('invalid request arguments'));
                return -1;
            }

            return 0;
        };

        /* debug helper*/
        $this->globals->debug = function(Client $c, $fmt, ... $argv) {
            $this->logmessage(self::LOG_DEBUG, "%s $fmt", $c->toString(), ... $argv);
        };

        /**
         * SwooleServer abilities
         */
        $this->globals->getServer = function() {
            return $this;
        };
        $this->globals->getClient = function($pid) {
            $fd = $this->globals->redis->hget('zhanhd:ht:onlines', $pid);
            if (empty($fd)) return null;
            return $this->clients->get($fd);
        };
        $this->globals->close = function($fd){
            return $this->swServer->close($fd);
        };
        $this->globals->sendTo = function($pid, ReqResInterface $reqres) {
            $fd = $this->globals->redis->hget('zhanhd:ht:onlines', $pid);
            if (empty($fd)) return false;
            return $this->swServer->send($fd, $reqres->encode());
        };
        $this->globals->task = function($name, ReqResInterface $data = null) {
            $code = $this->taskMap->get($name);
            $req = new Task;
            $req->setup($code, $data);
            $this->swServer->task($req->encode());
        };
        $this->globals->sendMessageTo = function($pid, ReqResInterface $reqres) {
            $workid = $this->globals->redis->hget('zhanhd:ht:onlines:workerid', $pid);
            if (empty($workid)) {
                return false;
            }
            $pipe = new Pipe;
            $pipe->setup($pid, $reqres);
            print_r($pipe);
            $this->swServer->sendMessage($pipe->encode(), $workid);
        };

        /* zone open timestamp */
        $zone = new Zone;
        if (false === $zone->find('openday')) {
            $zone->k = 'openday';
            $zone->v = strtotime(date('Y-m-d'));
            try {
                $zone->save();
            } catch (PDOException $e) {
                /* ignore */
            }
        }
        $this->globals->zoneOpenTime = $zone->v;
        $this->globals->getDayFromZoneOpen = function() {
            return ceil(($this->globals->time - $this->globals->zoneOpenTime)/86400);
        };

        $this->globals->setTime = function($ustime){
            $this->globals->ustime = $ustime;
            $this->globals->time   = (int)($ustime/1000000);
            $this->globals->epoch  = $this->globals->time - Store::EPOCH;
            $day = (int)date('Ymd', $this->globals->time);
            if ($this->globals->date != $day) {
                $this->globals->date = $day;
                $this->globals->week = (int)($this->globals->epoch/604800);
                $this->globals->month= (int)date('Ym', $this->globals->time);
            }
        };
    },

    /**
     * application-connect-callback
     */
    'connect' => function(Client $c) {
        $c->binding(new ClientBinding);
        if (false === $c->flags->bithas(Client::FLAG_MONITOR)) {
            $fd = $c->sock->intval();
            if ($fd == 0) {
                return;
            }

            $stmt = $this->globals->pdo->prepare('select count(1) from `zhanhd.player`.`Zone` where k=?');
            $stmt->execute([
                'whitelist'
            ]);
            $switch = (boolean)$stmt->fetchColumn(0);
            if ($switch) {
                $ip = $c->host->strval();
                $stmt = $this->globals->pdo->prepare('select count(1) from `zhanhd.player`.`ZoneWhiteList` where `ip`=?');
                $stmt->execute([
                    $ip,
                ]);
                $iswhite = (boolean)$stmt->fetchColumn(0);
                if (false === $iswhite) {
                    $this->swServer->send($fd, $this->globals->errorResponse->error('maintaining')->encode());
                    $c->flags->bitset(Client::FLAG_CLOSING);
                }
            }
        }
    },

    /**
     * application-close-callback
     */
    'close' => function(Client $c) {
        if ($c->login->intval()) {
            LogoutModule::aspect($c, $this->globals);
        }
    },

    /**
     * application-request-startup-callback
     */
    'requestStartup' => function(Client $c, Object $o) {
        AchievementModule::$notify = array();
        NewzoneMissionModule::$notify = [];
        WeekMissionModule::$notify = [];
        $this->globals->uplineup = false;
        if ($o->flags & self::CMD_LOGIN) {
            if (null === $c->local->player) {
                $c->local->player = new Player;
                $c->local->player->find($c->login->intval());
            }

            if (false === $c->local->player->userValidateStatus()) {
                throw new Exception('user not valid');
            }

            // counter-cycle-changed
            $lastCounterCycle = $c->mixed->counterCycle->intval();
            if ($lastCounterCycle && $lastCounterCycle <> $this->globals->date) {
                /* change counters */
                $c->local->player->counterCycle->setCycle($this->globals->date);
                $c->local->player->counterWeekly->setWeek($this->globals->week);
                $c->local->player->counterMonthly->setWeek($this->globals->month);

                // trigger achievement event
                $am = new AchievementModule($c->local->player, $this->globals);
                $am->trigger((new Object)->import(array(
                    'cmd' => 'signin',
                )));

                $am->trigger((new Object)->import(array(
                    'cmd' => 'memcard',
                )));
            }

            $c->mixed->counterCycle->intval($this->globals->date);
        }

        // action-log
        $c->local->logger = new Object;
    },

    /**
     * application-request-shutdown-callback
     */
    'requestShutdown' => function(Client $c, Object $o) {
        if (($n = count(AchievementModule::$notify))) {
            $r = new AchievementUpdateResponse;
            $r->notify->resize($n); $i = 0; foreach (AchievementModule::$notify as list($a, $pa)) {
                $r->notify->get($i++)->fromOwnerObject($a, $c->local->player, $pa);
            }

            $c->addReply($r);
        }

        if ($n = count(NewzoneMissionModule::$notify)) {
            $r = new NewzoneMissionUpdateResponse;
            $r->missions->resize($n);
            $i = 0;
            foreach (NewzoneMissionModule::$notify as list($m, $pm)) {
                $r->missions->get($i++)->fromObject($m, $c->local->player, $pm);
            }
            $c->addReply($r);
        }

        if (($n = count(WeekMissionModule::$notify))) {
            $r = new WeekMissionUpdateResponse;
            $r->missions->resize($n);
            $i = 0;
            foreach (WeekMissionModule::$notify as list($m, $pm)) {
                $r->missions->get($i++)->fromObject($m, $c->local->player, $this->globals, $pm);
            }
            $c->addReply($r);
        }

        if (($o->flags & self::CMD_IGNORE_LAST_CMD) == 0) {
            // remember-last-command
            $c->mixed->lastcmd->intval($o->code);
        }

        foreach ($c->reply as $x) {
            if ($x instanceof ErrorResponse) {
                $this->logmessage(self::LOG_DEBUG, 'error="%s", client: %s', $x->error, $c->toString());
            }
        }

        foreach ($c->local->logger as $x) {
            $this->logmessage(self::LOG_DEBUG, '%s pid=%d cmd=%d eid=%d cnt=%d peid=%d',
                $c->toString(), $c->local->player->id, $o->code,
                $x->eid,
                $x->cnt,
                $x->peid
            );
        }

        /* cache for updating rank */
        if ($c->local->player && $this->globals->uplineup) {
            $pl = $c->local->player->getLineup(1);
            $m = new RankModule;
            $new = $pl->getLvlsum();
            if ($new != $pl->lvlsum) {
                $pl->lvlsum = $new;
                $m->using(RankModule::KEY_PLAYER_LEVEL);
                $m->push($c->local->player->id, $pl->lvlsum, $this->globals->time);
            }

            $new = $pl->getPower();
            if ($new != $pl->power) {
                $pl->power = $new;
                $m->using(RankModule::KEY_PLAYER_POWER);
                $m->push($c->local->player->id, $pl->power, $this->globals->time);

                if (($gm = $c->local->player->getGuildMember())) {
                    /* guild rank */
                    $x = new UpdateGuildRank;
                    $x->gid->intval($gm->gid);
                    $this->globals->task('guild-rank', $x);
                }
            }
            $pl->save();
        }
    },

    /**
     * php-ini-parser
     */
    'PhpIniParser' => function($name, ... $args) {
        if (false === ($cfg = get_cfg_var($name))) {
            throw new InvalidArgumentException("PHP configuration named '$name' does not exist");
        }

        $o = (new Object)->import(array(
            'cfg' => $cfg,
        ));

        foreach ($args as $arg) {
            if (preg_match("~$arg=([^;]+)~", $cfg, $matches)) {
                $o->$arg = end($matches);
            }
        }

        return $o;
    },

    /* cleanup */
    'cleanup' => function() {
    },

    /* stop */
    'stop' => function(){
        $this->globals->redis->del('zhanhd:ht:onlines');
        $list = $this->globals->redis->keys('zhanhd:ht:guildmembers:*');
        foreach ($list as $key) {
            $this->globals->redis->del($key);
        }
    },

];
