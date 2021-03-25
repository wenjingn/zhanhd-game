<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension;

/**
 *
 */
use System\Stdlib\Object,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\Extension\Combat\Module as CombatModule,
    Zhanhd\ReqRes\CombatProcessInfo,
    Zhanhd\ReqRes\Top32\Info,
    Zhanhd\ReqRes\Top32\CompetitionInfo,
    Zhanhd\Object\Player,
    Zhanhd\Object\Message,
    Zhanhd\Object\Player\Lineup as PlayerLineup,
    Zhanhd\ReqRes\SysMsgResponse;

/**
 *
 */
class Top32
{
    /**
     * @var Redis
     */
    public $redis;

    /**
     * @var string
     */
    public $lastweek;
    public $dow;
    public $tod;
    public $top32Key;
    public $top32ReplayKey;

    /**
     * @const integer
     */
    const STATUS_UNDEF= 0;
    const STATUS_INIT = 1;
    const STATUS_CALC = 2;

    /**
     * @const integer
     */
    const TOP32_BEGINDOW = 2;
    const TOP32_BEGINTIM = 43200;
    const TOP32_INTERVAL = 60;

    const TOP32_CHAMPIONDOW = 6;
    const TOP32_CHAMPIONTIM = 43260;

    /**
     * @const string
     */
    const TOP32INFOKEY = 'zhanhd:ht:top32-info:%d';
    const TOP32KEY     = 'zhanhd:lt:top32:%d';
    const TOP32REPLAYKEY = 'zhanhd:ht:top32-replay:%d';
    const TOP32MSGKEY  = 'zhanhd:ht:top32-gmsg:%d';

    /**
     * @param Global $g
     * @return void
     */
    public function __construct($g)
    {
        $this->redis = $g->redis;
        $this->lastweek = $g->week-1;
        $this->nextweek = $g->week+1;
        $this->dow = date('w', $g->time);
        $this->tod = $g->epoch%86400;
        $this->top32InfoKey = sprintf(self::TOP32INFOKEY, $g->week);
        $this->top32Key = sprintf(self::TOP32KEY, $g->week);
        $this->top32ReplayKey = sprintf(self::TOP32REPLAYKEY, $g->week);
        $this->top32MsgKey = sprintf(self::TOP32MSGKEY, $g->week);
    }

    /**
     * @return void
     */
    public function clean()
    {
        $this->redis->del($this->top32InfoKey);
        $this->redis->del($this->top32Key);
        $this->redis->del($this->top32ReplayKey);
        $this->redis->del($this->top32MsgKey);
    }

    /**
     * @return void
     */
    public function init()
    {
        /* 周日运行: 周一——周日是一个周期. 所以周日要初始化下一周的数据 */
        $infoKey = sprintf(self::TOP32INFOKEY, $this->nextweek);
        $key     = sprintf(self::TOP32KEY, $this->nextweek);
        $msgKey  = sprintf(self::TOP32MSGKEY, $this->nextweek);
        if ($this->redis->hget($infoKey, 'status')) {
            return;
        }
        $top32 = $this->redis->zrange('zhanhd:st:pvp', 0, 31);
        foreach ($top32 as $pid) {
            $email = new Message;
            $email->pid = $pid;
            $email->tag = Message::TAG_TOP32_SELECTED;
            $email->save();
        }

        $seeds = array_splice($top32, 0, 8);
        $top32 = array_pad($top32, 24, 0);
        $seeds = array_pad($seeds, 8, 0);
        shuffle($seeds);
        shuffle($top32);
        $groups = array_chunk($top32, 3);
        for ($i = 0; $i < 8; $i++) {
            $groups[$i][] = array_pop($seeds);
            shuffle($groups[$i]);
        }
        
        $top32 = [];
        foreach ($groups as $group) {
            foreach ($group as $o) {
                $top32[] = $o;
            }
        }

        $fighters = [];
        foreach ($top32 as $pid) {
            $o = new Object;
			$fighters[] = $o;
            if ($pid == 0) continue;

            $p = new Player;
            $p->find($pid);
            $pl = new PlayerLineup;
            $pl->find($pid, 1);
            $o->p = $p;
            $o->pl = $pl;
        }
        $competitions = new Set(new CompetitionInfo);
        $competitions->resize(16);
        for ($i = 0; $i < 16; $i++) {
            $attacker = $fighters[$i*2];
            $defender = $fighters[$i*2+1];
            
            $competition = $competitions->get($i);
            $competition->index->intval($i);
			$competition->fromObject(
				$attacker->p, $attacker->pl ? $attacker->pl->getCaptain()->pe->e->id : 0, 
				$defender->p, $defender->pl ? $defender->pl->getCaptain()->pe->e->id : 0
			);
            $competition->status->intval(CompetitionInfo::STATUS_WAIT);
        }

        $this->redis->rpush($key, ...$top32);
        $this->redis->expire($key, 604800);
        $this->redis->hset($infoKey, 'status', self::STATUS_INIT);
        $this->redis->hset($infoKey, 'competitions', $competitions->encode());
        $this->redis->expire($infoKey, 604800);
        $this->redis->hset($msgKey, 'expire', 1);
        $this->redis->expire($msgKey, 604800);
    }

    /**
     * @var integer
     */
    private $retval = null;

    /**
     * @return integer
     */
    public function getRetval()
    {
        if ($this->retval === null) {
            $this->retval = $this->redis->hget($this->top32InfoKey, 'retval');
        }
        return $this->retval;
    }

    /**
     * @return void
     */
    public function run()
    {
        if ($this->redis->hget($this->top32InfoKey, 'status') != self::STATUS_INIT) {
            return;
        }
        $list = $this->redis->lrange($this->top32Key, 0, 31);
        $fighters = [];
        foreach ($list as $pid) {
            $o = new Object;
			$fighters[] = $o;
            if ($pid == 0) continue;
            $p = new Player;
            $p->find($pid);
            $pl = new PlayerLineup;
            $pl->find($pid, 1);

            $o->p = $p;
            $o->pl = $pl;
        }

        $competitions = new Set(new CompetitionInfo);
        $competitions->resize(32);
        $championInfo = new Info;
        $semifinal = false;
        $semiloser = [];
        $allover  = false;
        $i = 0;
        while (1) {
            $attacker = array_shift($fighters);
            $defender = array_shift($fighters);

            $competition = $competitions->get($i);
            $competition->index->intval($i);
			$competition->fromObject(
				$attacker->p, $attacker->pl ? $attacker->pl->getCaptain()->pe->e->id : 0, 
				$defender->p, $defender->pl ? $defender->pl->getCaptain()->pe->e->id : 0
			);
            $competition->status->intval(CompetitionInfo::STATUS_FINISH);

            $inf = new CombatProcessInfo;
            $m = new CombatModule;
            $m->combat($attacker->pl, $defender->pl, $inf);
            if ($inf->win->intval() == 1) {
                $fighters[] = $attacker;
                $this->setWinner($i, 0);
                if ($semifinal) {
                    $semiloser[] = $defender;
                }
            } else {
                $fighters[] = $defender;
                $this->setWinner($i, 1);
                if ($semifinal) {
                    $semiloser[] = $attacker;
                }
                $competition->result->intval(1);
            }
            $this->redis->hset($this->top32ReplayKey, $i, $inf->encode());

            $alives = count($fighters);
            if ($alives == 4) {
                $semifinal = true;
            } else if ($alives == 2) {
                $semifinal = false;
            } else if ($alives == 1) {
                if ($allover === false) {
                    $champion = array_shift($fighters);
                    $championInfo->fromPlayerObject($champion->p, $champion->rank, $champion->pl->getCaptain()->pe->e->id);
                    $fighters = $semiloser;
                    $allover = true;
                } else {
                    break;
                }
            }
            $i++;
        }

        $this->redis->hset($this->top32InfoKey, 'competitions', $competitions->encode());
        $this->redis->hset($this->top32InfoKey, 'champion', $championInfo->encode());
        $this->redis->hset($this->top32InfoKey, 'retval', $this->retval);
        $this->redis->hset($this->top32InfoKey, 'status', self::STATUS_CALC);
        $this->redis->expire($this->top32ReplayKey, 86400*14);
    }

    /**
     * @param integer $index
     * @param integer $winer
     * @return void
     */
    private function setWinner($index, $winer)
    {
        if ($this->retval === null) $this->retval = 0;
        $index = 31-$index;
        $this->retval |= $winer << $index;
    }

    /**
     * @return string|false
     */
    public function getChampion()
    {
        if ($this->dow == self::TOP32_CHAMPIONDOW && $this->tod >= self::TOP32_CHAMPIONTIM) {
            return $this->redis->hget($this->top32InfoKey, 'champion');
        }
        return $this->redis->hget(sprintf(self::TOP32INFOKEY, $this->lastweek), 'champion');
    }

    /**
     * @param Set $competitions
     * @return void
     */
    public function getCompetitionInfo($competitions)
    {
        if ($this->dow == 0 && $this->tod < 22*3600) {
            $competitionsBin = $this->redis->hget(sprintf(self::TOP32INFOKEY, $this->lastweek), 'competitions');
            if ($competitionsBin) {
                $competitions->decode($competitionsBin);
            }
            return;
        }
        if (false === $this->redis->hget($this->top32InfoKey, 'status')) {
            return;
        }

        if ($this->dow < 2 || ($this->dow == 2 && $this->tod < 11*3600+1800)) {
            $competitionsBin = $this->redis->hget($this->top32InfoKey, 'competitions');
            if ($competitionsBin) {
                $competitions->decode($competitionsBin);
            }
            return;
        }

        if ($this->dow == 2 && $this->tod < 43200) {
            $competitionsBin = $this->redis->hget($this->top32InfoKey, 'competitions');
            if ($competitionsBin) {
                $competitions->decode($competitionsBin);
                $competitions->sub(0, 16);
                foreach ($competitions as $o) {
                    $o->status->intval(CompetitionInfo::STATUS_WAIT);
                }
            }
            return;
        }

        $dayover = 43200 + pow(2, 6-$this->dow)*self::TOP32_INTERVAL;
        if ($this->dow == 6) $dayover += self::TOP32_INTERVAL;
        $dow = $this->tod > $dayover ? $this->dow+1 : $this->dow;
        $count = $dow >= 6 ? 32 : 32-pow(2, 6-$dow);

        $competitionsBin = $this->redis->hget($this->top32InfoKey, 'competitions');
        if (false === $competitionsBin) return;
        $competitions->decode($competitionsBin);
        $competitions->sub(0, $count);
        if ($this->tod > $dayover) {
            if ($dow > 6) {
                $remain = 0;
            } else if ($dow == 6) {
                $remain = 2;
            } else {
                $remain = pow(2, 6-$dow);
            }
        } else {
            $tod = max(43200, $this->tod);
            $remain = ceil(($dayover-$tod)/self::TOP32_INTERVAL);
        }
        while ($remain > 0) {
            $o = $competitions->get($count-$remain);
            $o->status->intval(CompetitionInfo::STATUS_WAIT);
            $o->result->intval(0);
            $remain--;
        }
    }

    /**
     * @return integer
     */
    public function competitionFinishedCount()
    {
        if ($this->dow < 2) return 0;
        $count = 32 - pow(2, 7-$this->dow);
        if ($this->tod < 43200) {
            return $count;
        }
        $todayCount = $this->dow == 6 ? 2 : pow(2, 6-$this->dow);
        if ($this->tod >= 43200 + $todayCount*self::TOP32_INTERVAL) {
            return $count+$todayCount;
        }
        return $count + (int)(($this->tod-43200)/60);
    }

    /**
     * @param integer $index
     * @return string
     */
    public function getCombatBin($index)
    {
        return $this->redis->hget($this->top32ReplayKey, $index);
    }

    /**
     * @var integer
     */
    const GMSG_OVER = 45000;

    /**
     * @var array
     */
    private static $gmsg = [
        2 => [
            39600 => false,
            40500 => [
                'type'  => 'ready',
                'rhidx' => 0,
                'tmpid' => 7,
            ],
            41100 => [
                'type'  => 'ready',
                'rhidx' => 1,
                'tmpid' => 7,
            ],
            41400 => [
                'type'  => 'ready',
                'rhidx' => 2,
                'tmpid' => 7,
            ],
            43140 => false,
            43260 => [
                'type'  => 'ready',
                'rhidx' => 3,
                'tmpid' => 8,
            ],
            self::GMSG_OVER => [
                'type'   => 'competition',
                'tmpid0' => 9,
                'tmpid1' => 11,
            ],
        ],
        3 => [
            43140 => false,
            43260 => [
                'type' => 'ready',
                'rhidx' => 4,
                'tmpid' => 8,
            ],
            self::GMSG_OVER => [
                'type' => 'competition',
                'tmpid0' => 9,
                'tmpid1' => 11,
            ],
        ],
        4 => [
            43140 => false,
            43260 => [
                'type' => 'ready',
                'rhidx' => 5,
                'tmpid' => 8,
            ],
            self::GMSG_OVER => [
                'type' => 'competition',
                'tmpid0' => 9,
                'tmpid1' => 11,
            ],
        ],
        5 => [
            43140 => false,
            43260 => [
                'type' => 'ready',
                'rhidx' => 6,
                'tmpid' => 8,
            ],
            self::GMSG_OVER => [
                'type' => 'competition',
                'tmpid0' => 10,
                'tmpid1' => 11,
            ],
        ],
        6 => [
            43140 => false,
            43260 => [
                'type' => 'ready',
                'rhidx' => 7,
                'tmpid' => 8,
            ],
            43320 => [
                'type'  => 'third',
                'tmpid' => 12,
            ],
            self::GMSG_OVER => [
                'type'   => 'champion',
                'tmpid0' => 13,
                'tmpid1' => 14,
            ]
        ],
    ];

    /**
     * @param Global $g
     * @return void
     */
    public function globalMsg($g)
    {
        if ($this->tod > 45000) return;
        if (false === isset(self::$gmsg[$this->dow])) return;
        $cfg = self::$gmsg[$this->dow];
        foreach ($cfg as $tod => $o) {
            if ($this->tod >= $tod) continue;
            if ($o === false) break;
            switch ($o['type']) {
            case 'ready':
                if ($this->redis->hget($this->top32MsgKey, sprintf('ready%d', $o['rhidx'])) === false) {
                    $this->redis->hset($this->top32MsgKey, sprintf('ready%d', $o['rhidx']), 1);
                    $smr = new SysMsgResponse;
                    $smr->format($o['tmpid'], 2);
                    $g->task('broadcast-global', $smr);
                }
                break;
            case 'competition':
                $index = $this->getCompetitionMessageIndex();
                if ($this->redis->hget($this->top32MsgKey, sprintf('competition%d', $index)) === false) {
                    $this->redis->hset($this->top32MsgKey, sprintf('competition%d', $index), 1);
                    $competition = $this->getCompetitionDetail($index);
                    $attacker = new Player;
                    $attacker->find($competition['attacker']);
                    $defender = new Player;
                    $defender->find($competition['defender']);
                    if ($competition['win']) {
                        $winner = $defender;
                        $loser  = $attacker;
                    } else {
                        $winner = $attacker;
                        $loser  = $defender;
                    }

                    $argvs = [$winner->name, $loser->name];
                    if ($this->dow < 5) {
                        $argvs[] = pow(2, 6-$this->dow);
                    }
                    $smr = new SysMsgResponse;
                    $smr->format($o['tmpid0'], 2, $argvs);
                    $g->task('broadcast-global', $smr);
                }
                if ($this->tod > 43200+pow(2, 6-$this->dow)*self::TOP32_INTERVAL && $this->redis->hget($this->top32MsgKey, sprintf('dayover%d', $this->dow)) === false) {
                    $this->redis->hset($this->top32MsgKey, sprintf('dayover%d', $this->dow), 1);
                    $smr = new SysMsgResponse;
                    $smr->format($o['tmpid1'], 2);
                    $g->task('broadcast-global', $smr);
                }
                break;
            case 'third':
                if ($this->redis->hget($this->top32MsgKey, 'competition31') === false) {
                    $this->redis->hset($this->top32MsgKey, 'competition31', 1);
                    $competition = $this->getCompetitionDetail(31);
                    $attacker = new Player;
                    $attacker->find($competition['attacker']);
                    $defender = new Player;
                    $defender->find($competition['defender']);
                    if ($competition['win']) {
                        $winner = $defender;
                        $loser  = $attacker;
                    } else {
                        $winner = $attacker;
                        $loser  = $defender;
                    }
                    $smr = new SysMsgResponse;
                    $smr->format($o['tmpid'], 2, [$winner->name, $loser->name]);
                    $g->task('broadcast-global', $smr);
                }
                break;
            case 'champion':
                if ($this->redis->hget($this->top32MsgKey, 'competition30') === false) {
                    $this->redis->hset($this->top32MsgKey, 'competition30', 1);
                    $competition = $this->getCompetitionDetail(30);
                    $attacker = new Player;
                    $attacker->find($competition['attacker']);
                    $defender = new Player;
                    $defender->find($competition['defender']);
                    if ($competition['win']) {
                        $winner = $defender;
                        $loser  = $attacker;
                    } else {
                        $winner = $attacker;
                        $loser  = $defender;
                    }
                    $smr = new SysMsgResponse;
                    $smr->format($o['tmpid0'], 2, [$winner->name, $loser->name]);
                    $g->task('broadcast-global', $smr);
                }
                if ($this->tod > 43320 && $this->redis->hincrby($this->top32MsgKey, 'championborn', 1) <= 2) {
                    $list = [];
                    for ($i = 30; $i < 32; $i++) {
                        $info = $this->getCompetitionDetail($i);
                        if ($info['win']) {
                            $list[] = $info['defender'];
                            $list[] = $info['attacker'];
                        } else {
                            $list[] = $info['attacker'];
                            $list[] = $info['defender'];
                        }
                    }

                    $argvs = [];
                    foreach ($list as $pid) {
                        $p = new Player;
                        $p->find($pid);
                        $argvs[] = $p->name;
                    }
                    $smr = new SysMsgResponse;
                    $smr->format($o['tmpid1'], 2, $argvs);
                    $g->task('broadcast-global', $smr);
                }
                break;
            }
            break;
        }
    }

    /**
     * @return integer
     */
    private function getCompetitionMessageIndex()
    {
        if ($this->dow < 2) return false;
        if ($this->tod < 43260) return false;
        $maxcount = pow(2, 6-$this->dow);
        $count = min(ceil(($this->tod-43260)/self::TOP32_INTERVAL), $maxcount);
        return 32-pow(2,7-$this->dow)+$count-1;
    }

    /**
     * @param integer $index
     * @return void
     */
    public function getCompetitionDetail($index)
    {
        $retval = $this->getRetval();
        
        $flag = $index == 31 ? false : true;
        if ($index == 31) $index--;
        $attackerIndex = 30-(31-$index)*2;
        $defenderIndex = 31-(31-$index)*2;
        $set = $this->redis->lrange($this->top32Key, 0, 31);
        return [
            'attacker' => $set[$this->getTop32ByIndex($attackerIndex, $flag)],
            'defender' => $set[$this->getTop32ByIndex($defenderIndex, $flag)],
            'win' => (boolean)(1<<(31-$index)&$retval),
        ];
    }

    /**
     * @param integer $index
     * @param boolean $flag
     * @return integer
     */
    public function getTop32ByIndex($index, $flag = true)
    {
        $retval = $this->getRetval();
        if ($index > 30) return;

        $choosed = false;
        $index = 31-$index;
        while ($index < 32) {
            $winner = (boolean)(1 << $index & $retval);
            if ($choosed === false) {
                $winner ^= $flag;
                $choosed = true;
            } else {
                $winner = !$winner;
            }
            $index = $index*2+$winner;
        }
        return 63-$index;
    }

    /**
     * @return array
     */
    public function getRanks()
    {
        $retval = $this->getRetval();
        $ranks = [];
        $alives = range(0, 31);
        for ($i = 0; $i < 28; $i++) {
            $attacker = array_shift($alives);
            $defender = array_shift($alives);
            $win = (boolean)(1<<(31-$i)&$retval);
            if ($win) {
                $alives[] = $defender;
                array_unshift($ranks, $attacker);
            } else {
                $alives[] = $attacker;
                array_unshift($ranks, $defender);
            }
        }
        $losers = [];
        for ($i = 0; $i < 2; $i++) {
            $attacker = array_shift($alives);
            $defender = array_shift($alives);
            $win = (boolean)(1<<(3-$i)&$retval);
            if ($win) {
                $alives[] = $defender;
                $losers[] = $attacker;
            } else {
                $alives[] = $attacker;
                $losers[] = $defender;
            }
        }
        $win = (boolean)(1&$retval);
        array_unshift($ranks, $losers[!$win]);
        array_unshift($ranks, $losers[$win]);
        $win = (boolean)(2&$retval);
        array_unshift($ranks, $alives[!$win]);
        array_unshift($ranks, $alives[$win]);
        return $ranks;
    }

    /**
     * @return array
     */
    public function getTop32()
    {
        return $this->redis->lrange($this->top32Key, 0, 31);
    }
}
