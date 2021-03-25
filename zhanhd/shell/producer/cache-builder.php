<?php
/**
 * $Id$
 */

/**
 *
 */
require '/data/php/games/zhanhd/shell/bootstrap.php';

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Config\Army,
    Zhanhd\Config\Building,
    Zhanhd\Config\Crusade,
    Zhanhd\Config\Enhance,
    Zhanhd\Config\EntityGroup,
    Zhanhd\Config\Entity,
    Zhanhd\Config\Forge,
    Zhanhd\Config\EntityPicked,
    Zhanhd\Config\Formation,
    Zhanhd\Config\Goods,
    Zhanhd\Config\HeroExp,
    Zhanhd\Config\Skill,
    Zhanhd\Config\Instance,
    Zhanhd\Config\Instance\Event,
    Zhanhd\Config\Achievement,
    Zhanhd\Config\NPCLineup,
    Zhanhd\Config\Drop,
    Zhanhd\Config\Reward,
    Zhanhd\Config\SigninReward,
    Zhanhd\Config\Question,
    Zhanhd\Config\Merchandise,
    Zhanhd\Config\DepositReward,
    Zhanhd\Config\InviteReward,
    Zhanhd\Config\Battle,
    Zhanhd\Config\Battle\Diff as BattleDiff,
    Zhanhd\Config\GreenerReward,
    Zhanhd\Config\FriendShipGoods,
    Zhanhd\Config\QuestionReward,
    Zhanhd\Config\ActIns,
    Zhanhd\Config\ActIns\Floor as ActInsFloor,
    Zhanhd\Config\ResourceRecruit,
    Zhanhd\Config\FixedTimeReward,
    Zhanhd\Config\RechargeReward,
    Zhanhd\Config\Gift,
    Zhanhd\Config\NewzoneMission,
    Zhanhd\Config\WeekMission,
    Zhanhd\Config\PropGoods,
    Zhanhd\Config\DayIns,
    Zhanhd\Config\DayIns\Diff  as DayInsDiff,
    Zhanhd\Config\WorldBoss,
    Zhanhd\Config\Guild\Contribution,
    Zhanhd\Config\Guild\Exp as GuildExp,
    Zhanhd\Config\Guild\Gift as GuildGift,
    Zhanhd\Config\Guild\Chest as GuildChest,
    Zhanhd\Config\MessageTemplate,
    Zhanhd\Config\Activity,
    Zhanhd\Config\Activity\DiaRec,
    Zhanhd\Config\CompletionReward;

/**
 *
 */
class CacheBuilder
{
    /**
     * @var stdClass
     */
    protected $cache;

    /**
     * @var string
     */
    protected $destination;

    /**
     * @param  PDO $pdo
     * @return void
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo   = $pdo;
        $this->cache = new stdClass;
		$this->pdo->query('USE `zhanhd.config`');
        $this->initial();
    }

    /**
     * @return void
     */
    protected function initial()
    {
        $this->initArmy();
        $this->initBuilding();
        $this->initCrusade();
        $this->initEdrop();
        $this->initEgroup();
        $this->initEnhance();
        $this->initEntity();
        $this->initForge();
        $this->initHeroExp();
        $this->initFormation();
        $this->initGoods();
        $this->initSkill();
        $this->initEffects();
        $this->initInstance();
        $this->initAchievement();
        $this->initError();
        $this->initNPCLineup();
        $this->initDrop();
        $this->initReward();
        $this->initSigninReward();
        $this->initQuestion();
        $this->initMerchandise();
        $this->initDepositReward();
        $this->initInviteReward();
        $this->initBattle();
        $this->initGreenerReward();
        $this->initFriendShipGoods();
        $this->initQuestionReward();
        $this->initFixedTimeReward();
        $this->initRechargeReward();
        $this->initActIns();
        $this->initGift();
        $this->initLeader();
        $this->initResourceRecruit();
        $this->initNewzoneMission();
        $this->initNewzoneMissionIndexByType();
        $this->initWeekMissionTypeIndexByFlag();
        $this->initWeekMission();
        $this->initWeekMissionIndexByType();
        $this->initPropGoods();
        $this->initDayIns();
        $this->initWorldBoss();
        $this->initGuild();
        $this->initMessageTemplate();
        $this->initActivity();
		$this->initBond();
        $this->initCompletionReward();
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param  string $destination
     * @return void
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @param array
     * @param boolean $remain
     * @return void
     */
    public function filter($keys, $remain = true)
    {
        foreach ($this->cache as $k => $v) {
            if ($remain != in_array($k, $keys)) {
                unset($this->cache->$k);
            }
        }
    }

    /**
     * @return boolean
     */
    public function export()
    {
        return file_put_contents($this->destination, serialize($this->cache));
    }

    /**
     * @return void
     */
    protected function initArmy()
    {
        $this->cache->army = [];

        $stmt   = $this->pdo->query('SELECT * FROM `Army`');
        $armies = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `ArmyUpgradation`');
        $upgradations = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($armies as $o) {
            $data[$o->id] = $o;
        }

        foreach ($upgradations as $o) {
            $data[$o->aid]->upgradations[$o->k] = $o->v;
        }

        foreach ($data as $o) {
            $this->cache->army[$o->id] = new Army($o);
        }
    }

    /**
     * return void
     */
    protected function initBuilding()
    {
        $this->cache->building = [];

        $stmt = $this->pdo->query('SELECT * FROM `Building`');
        $buildings = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `BuildingProperty`');
        $properties = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `BuildingProduction`');
        $productions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `BuildingUpgradation`');
        $upgradations = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($buildings as $o) {
            $data[$o->id] = $o;
        }

        foreach ($properties as $o) {
            $data[$o->bid]->level[$o->lvl] = $o;
            unset($o->bid);
            unset($o->lvl);
        }

        foreach ($productions as $o) {
            $data[$o->bid]->level[$o->lvl]->productions[$o->k] = $o->v;
        }

        foreach ($upgradations as $o) {
            $data[$o->bid]->level[$o->lvl]->upgradations[$o->k] = $o->v;
        }

        foreach ($data as $o) {
            $this->cache->building[$o->id] = new Building($o);
        }
    }

    /**
     * @return void
     */
    protected function initCrusade()
    {
        $this->cache->crusade = [];

        $stmt = $this->pdo->query('SELECT * FROM `Crusade`');
        $crusades = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `CrusadeSource`');
        $sources = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `CrusadeResource`');
        $resources = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($crusades as $o) {
            $data[$o->id] = $o;
        }

        foreach ($sources as $o) {
            $data[$o->cid]->source[$o->k] = $o->v;
        }

        foreach ($resources as $o) {
            $data[$o->cid]->resource[$o->k] = $o->v;
        }

        foreach ($data as $o) {
            $this->cache->crusade[$o->id] = new Crusade($o);
        }
    }

    /**
     * @return void
     */
    protected function initEdrop()
    {
        $this->cache->edrop = [];

        $stmt = $this->pdo->query('SELECT * FROM `EntityPicked`');
        $picks = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `EntityPickedSource`');
        $source = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($picks as $o) {
            $data[$o->id] = $o;
        }

        foreach ($source as $o) {
            $data[$o->epid]->source[$o->k] = $o->v;
        }

        foreach ($data as $o) {
            $this->cache->edrop[$o->id] = new EntityPicked($o);
        }
    }

    /**
     * @return void
     */
    protected function initEgroup()
    {
        $this->cache->egroup = [];

        $stmt = $this->pdo->query('SELECT * FROM `EntityGroup`');
        $groups = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($groups as $o) {
            $data[$o->gid]->items[] = $o->eid;
        }

        foreach ($data as $k => $o) {
            $this->cache->egroup[$k] = new EntityGroup($o);
        }
    }

    /**
     * @return void
     */
    protected function initEnhance()
    {
        $this->cache->enhance = [];

        $stmt = $this->pdo->query('SELECT * FROM `EntityEnhance` order by `lvl`');
        $enhances = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($enhances as $o) {
            $data[$o->lvl] = $o;
        }

        foreach ($data as $o) {
            $this->cache->enhance[$o->lvl] = new Enhance($o);
        }
    }

    /**
     * @return void
     */
    protected function initEntity()
    {
        $this->cache->entity = [];

        $stmt = $this->pdo->query('SELECT * FROM `Entity`');
        $entities = $stmt->fetchAll(PDO::FETCH_OBJ);

        /**
         * equip
         */
        $stmt = $this->pdo->query('SELECT * FROM `EntityUseRule`');
        $rules = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `EntityEquipEffect`');
        $effects = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `EntityDecomposed`');
        $decomposed = $stmt->fetchAll(PDO::FETCH_OBJ);

        /**
         * hero
         */
        $stmt = $this->pdo->query('SELECT * FROM `EntityArmy`');
        $armies = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `EntitySkill`');
        $skills = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `EntityProperty`');
        $properties = $stmt->fetchAll(PDO::FETCH_OBJ);

        /* randpack */
        $stmt = $this->pdo->query('SELECT * FROM `EntityDrop`');
        $drops = $stmt->fetchAll(PDO::FETCH_OBJ);

		/* bond */
		$stmt = $this->pdo->query('SELECT * FROM `BondMember`');
		$bonds = $stmt->fetchAll(PDO::FETCH_OBJ);

        /**
         *
         */
        $data = [];
        foreach ($entities as $o) {
            $data[$o->id] = $o;
        }

        foreach ($rules as $o) {
            $data[$o->eid]->rules[$o->k] = $o->v;
        }

        foreach ($effects as $o) {
            $data[$o->eid]->effects[$o->k] = $o->v;
        }

        foreach ($decomposed as $o) {
            $data[$o->eid]->decomposed[$o->k] = $o->v;
        }

        foreach ($armies as $o) {
            $data[$o->eid]->army[$o->k] = $o->v;
        }

        foreach ($skills as $o) {
            $data[$o->eid]->skills[$o->k] = $o->v;
        }

        foreach ($properties as $o) {
            if (!isset($data[$o->eid]->property)) {
                $data[$o->eid]->property = new stdClass;
            }
            $data[$o->eid]->property->{$o->k} = $o->v;
        }

        foreach ($drops as $o) {
            $data[$o->eid]->drops[] = $o;
            unset($o->eid);
        }

		foreach ($bonds as $o) {
			$data[$o->eid]->bonds[] = $o->bid;
		}

        /**
         *
         */
        foreach ($data as $o) {
            if (isset($o->army)) {
                asort($o->army);
            }

            if (isset($o->skills)) {
                arsort($o->skills);
            }

            $this->cache->entity[$o->id] = new Entity($o);
        }
    }

    /**
     * @return void
     */
    protected function initForge()
    {
        $this->cache->forge = [];

        $stmt = $this->pdo->query('SELECT * FROM `Forge`');
        $sets = $stmt->fetchAll(PDO::FETCH_OBJ);

        /**
         *
         */
        foreach ($sets as $o) {
            $this->cache->forge[$o->id] = new Forge($o);
        }
    }

    /**
     * @return void
     */
    protected function initHeroExp()
    {
        $this->cache->heroexp = [];

        $stmt = $this->pdo->query('SELECT `lvl`,`exp` FROM `EntityExperience` WHERE `type`=20 order by `lvl`');
        $exps = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($exps as $o) {
            $data[$o->lvl] = $o;
        }

        foreach ($data as $o) {
            $this->cache->heroexp[$o->lvl] = new HeroExp($o);
        }
    }

    /**
     * @return void
     */
    protected function initFormation()
    {
        $this->cache->formation = [];

        $stmt = $this->pdo->query('SELECT * FROM `Formation`');
        $formations = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `FormationAddition`');
        $additions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `FormationRestraint`');
        $restraints = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($formations as $o) {
            $data[$o->id] = $o;
        }

        foreach ($additions as $o) {
            $data[$o->fid]->additions[$o->aid][$o->eid] = $o->v;
        }

        foreach ($restraints as $o) {
            $data[$o->fid]->restraints[$o->rid] = 1;
        }

        foreach ($data as $o) {
            $this->cache->formation[$o->id] = new Formation($o);
        }
    }

    /**
     * @return void
     */
    protected function initGoods()
    {
        $this->cache->goods = [];

        $stmt  = $this->pdo->query('SELECT * FROM `Goods`');
        $goods = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt  = $this->pdo->query('SELECT * FROM `GoodsRequirement`');
        $requires = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($goods as $o) {
            $data[$o->id] = $o;
        }

        foreach ($requires as $o) {
            $data[$o->gid]->requirement[$o->k] = $o->v;
        }

        foreach ($data as $o) {
            $this->cache->goods[$o->id] = new Goods($o);
        }
    }

    /**
     * @return void
     */
    protected function initSkill()
    {
        $stmt = $this->pdo->query('SELECT * FROM `Skill`');
        $skills = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `SkillEffect`');
        $effects = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($skills as $o) {
            $data[$o->id] = $o;
        }

        foreach ($effects as $o) {
            $data[$o->sid]->effects[$o->eid] = $o;
            unset($o->sid);
        }

        /* difference with other */
        $this->cache->skill= $data;
    }

    /**
     *
     * @return void
     */
    protected function initEffects()
    {
        $stmt = $this->pdo->query('SELECT * FROM `Effect`');
        $objs = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($objs as $o) {
            $data[$o->id] = $o;
        }

        $this->cache->effects = $data;
    }

    /**
     * @return void
     */
    protected function initInstance()
    {
        $stmt = $this->pdo->query('SELECT * FROM `Instance`');
        $ins = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query("SELECT * FROM `InsEvt`");
        $events = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `InsEvtPrev`');
        $prevs = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `InsEvtNext`');
        $nexts = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `InsEvtNpc`');
        $npc = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `InsEvtDrop`');
        $drop = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `InsEvtDropSource`');
        $source  = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($ins as $o) {
            $data[$o->diff][$o->id] = $o;
        }

        foreach ($events as $o) {
            $data[$o->diff][$o->iid]->events[$o->evt] = $o;
            unset($o->diff);
            unset($o->iid);
        }

        foreach ($prevs as $o) {
            $data[$o->diff][$o->iid]->events[$o->evt]->prev[$o->k] = 1;
        }

        foreach ($nexts as $o) {
            $data[$o->diff][$o->iid]->events[$o->evt]->next[$o->k] = $o->v;
        }

        foreach ($npc as $o) {
            $data[$o->diff][$o->iid]->events[$o->evt]->npc[$o->pos] = $o;
            unset($o->diff);
            unset($o->iid);
            unset($o->evt);
            unset($o->pos);
        }

        foreach ($drop as $o) {
            $data[$o->diff][$o->iid]->events[$o->evt]->drop[$o->index] = $o;
            unset($o->diff);
            unset($o->iid);
            unset($o->evt);
            unset($o->index);
        }

        foreach ($source as $o) {
            $data[$o->diff][$o->iid]->events[$o->evt]->drop[$o->index]->items[$o->eid] = $o->num;
        }

        foreach ($data as $diff => $instances) {
            $key = 'ins'.$diff;
            $list = [];
            foreach ($instances as $ins) {
                $events = $ins->events;
                unset($ins->events);
                foreach ($events as $e) {
                    $ins->events[$e->evt] = new Event($e);
                }
                $list[$ins->id] = new Instance($ins);
            }
            $this->cache->$key = $list;
        }
    }

    /**
     * @return void
     */
    protected function initAchievement()
    {
        $this->cache->achievement = [];

        $stmt = $this->pdo->query('SELECT * FROM `Achievement`');
        $objs = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `AchievementSource`');
        $sors = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($objs as $o) {
            $data[$o->id] = $o;
            $data[$o->id]->sources = [];
        }

        foreach ($sors as $o) {
            $data[$o->aid]->sources[$o->k] = $o->v;
        }

        foreach ($data as $o) {
            $this->cache->achievement[$o->id] = new Achievement($o);
        }
    }

    /**
     * @return void
     */
    protected function initError()
    {
        $this->cache->error = [];

        $stmt   = $this->pdo->query('SELECT * FROM `Error`');
        $errors = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($errors as $o) {
            $this->cache->error[$o->error] = $o->code;
        }
    }

    /**
     * @return void
     */
    protected function initNPCLineup()
    {
        $this->cache->npc = [];

        $stmt = $this->pdo->query('SELECT * FROM `NPCLineup`');
        $npcs = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt  = $this->pdo->query('SELECT * FROM `NPCLineupHero`');
        $heros = $stmt->fetchAll(PDO::FETCH_OBJ);

        /**
         *
         */
        $data = [];

        foreach ($npcs as $o) {
            $data[$o->id] = $o;
        }

        foreach ($heros as $o) {
            $data[$o->lid]->combatants[$o->pos] = $o;
            unset($o->lid);
        }

        /**
         *
         */
        foreach ($data as $o) {
            $this->cache->npc[$o->id] = new NPCLineup($o);
        }
    }

    /**
     * @return void
     */
    protected function initDrop()
    {
        $this->cache->drop = [];

        $stmt = $this->pdo->query('SELECT * FROM `Drop`');
        $drop = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt    = $this->pdo->query('SELECT * FROM `DropSource`');
        $sources = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($drop as $o) {
            $data[$o->id]->items[$o->index] = $o;
            unset($o->id);
            unset($o->index);
        }

        foreach ($sources as $o) {
            $data[$o->id]->items[$o->index]->source[$o->k] = $o->v;
        }

        foreach ($data as $k => $o) {
            $this->cache->drop[$k] = new Drop($o);
        }
    }

    /**
     * @return void
     */
    protected function initReward()
    {
        $this->cache->reward = [];

        $stmt    = $this->pdo->query('SELECT * FROM `Reward`');
        $rewards = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt  = $this->pdo->query('SELECT * FROM `RewardRank` order by `index`');
        $ranks = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `RewardRankSource`');
        $rankSource = $stmt->fetchAll(PDO::FETCH_OBJ);
        $stmt = $this->pdo->query('SELECT * FROM `RewardRankCoherence`');
        $rankCoherence = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `RewardScore`');
        $scores = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `RewardScoreSource`');
        $scoreSource = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($rewards as $o) {
            $data[$o->id] = $o;
        }

        foreach ($ranks as $o) {
            $data[$o->rid]->ranks[$o->index] = $o;
            unset($o->rid);
        }

        foreach ($rankSource as $o) {
            $data[$o->rid]->ranks[$o->index]->source[$o->k] = $o->v;
        }
        
        foreach ($rankCoherence as $o) {
            $data[$o->rid]->ranks[$o->index]->coherence[$o->k] = $o->v;
        }

        foreach ($scores as $o) {
            $data[$o->rid]->scores[$o->index] = $o;
            unset($o->rid);
        }

        foreach ($scoreSource as $o) {
            $data[$o->rid]->scores[$o->index]->source[$o->k] = $o->v;
        }

        foreach ($data as $o) {
            $this->cache->reward[$o->id] = new Reward($o);
        }
    }

    /**
     * @return void
     */
    protected function initSigninReward()
    {
        $this->cache->signin = [];

        $stmt = $this->pdo->query('SELECT * FROM `SigninReward`');
        $rewards = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($rewards as $o) {
            $this->cache->signin[$o->dom] = new SigninReward($o);
        }
    }

    /**
     * @return void
     */
    protected function initQuestion()
    {
        $this->cache->question = [];

        $stmt = $this->pdo->query("SELECT * FROM `Question`");
        $questions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($questions as $o) {
            $data[$o->id] = $o;
            unset($o->question);
        }

        foreach ($data as $o) {
            $this->cache->question[$o->id] = new Question($o);
        }
    }

    /**
     * @return void
     */
    protected function initMerchandise()
    {
        $this->cache->merchandise = [];

        $stmt = $this->pdo->query('SELECT * FROM `Merchandise`');
        $merchandises = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($merchandises as $o) {
            $data[$o->id] = $o;
        }

        foreach ($data as $o) {
            $this->cache->merchandise[$o->id] = new Merchandise($o);
        }
    }

    /**
     * @return void
     */
    protected function initDepositReward()
    {
        $this->cache->deposit = [];
        $stmt = $this->pdo->query('SELECT * FROM `DepositReward`');
        $rewards = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `DepositRewardSource`');
        $sources = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($rewards as $o) {
            $data[$o->id] = $o;
        }

        foreach ($sources as $o) {
            $data[$o->drid]->source[$o->eid] = $o->num;
        }

        foreach ($data as $o) {
            $this->cache->deposit[$o->id] = new DepositReward($o);
        }
    }

    /**
     * @return void
     */
    protected function initInviteReward()
    {
        $this->cache->invite = [];
        $stmt = $this->pdo->query('SELECT * FROM `InviteReward`');
        $rewards = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `InviteRewardSource`');
        $sources = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($rewards as $o) {
            $data[$o->id] = $o;
        }

        foreach ($sources as $o) {
            $data[$o->irid]->source[$o->eid] = $o->num;
        }

        foreach ($data as $o) {
            $this->cache->invite[$o->id] = new InviteReward($o);
        }
    }

    /**
     * @return void
     */
    protected function initBattle()
    {
        $this->cache->battle = [];
        $stmt = $this->pdo->query("SELECT * FROM `Battle`");
        $battles = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `BattleReward`');
        $drops = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($battles as $o) {
            if (false === isset($data[$o->id])) {
                $data[$o->id] = new stdClass;
                $data[$o->id]->id = $o->id;
            }
            $data[$o->id]->diff[$o->diff] = $o;
            unset($o->id);
        }

        foreach ($drops as $o) {
            $data[$o->bid]->diff[$o->diff]->drops[$o->index] = $o;
            unset($o->bid);
            unset($o->index);
        }

        foreach ($data as $o) {
            foreach ($o->diff as $d => $diff) {
                $data[$o->id]->diff[$d] = new BattleDiff($diff);
            }
            $this->cache->battle[$o->id] = new Battle($o);
        }
    }

    /**
     * @return void
     */
    protected function initGreenerReward()
    {
        $this->cache->greenerReward = [];
        $stmt = $this->pdo->query('SELECT * FROM `GreenerReward`');
        $rewards = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($rewards as $o) {
            $data[$o->day] = $o;
        }

        foreach ($data as $o) {
            $this->cache->greenerReward[$o->day] = new GreenerReward($o);
        }
    }

    /**
     * @return void
     */
    protected function initFriendShipGoods()
    {
        $this->cache->friendShipGoods = [];
        $stmt = $this->pdo->query('SELECT * FROM `FriendShipGoods`');
        $goods = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($goods as $o) {
            $data[$o->id] = $o;
        }

        foreach ($data as $o) {
            $this->cache->friendShipGoods[$o->id] = new FriendShipGoods($o);
        }
    }

    /**
     * @return void
     */
    protected function initQuestionReward()
    {
        $this->cache->questionReward = [];
        $stmt = $this->pdo->query('SELECT * FROM `QuestionReward`');
        $rewards = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `QuestionRewardSource`');
        $sources = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($rewards as $o) {
            $data[$o->id] = $o;
        }

        foreach ($sources as $o) {
            $data[$o->qrid]->source[$o->eid] = $o->num;
        }

        foreach ($data as $o) {
            $this->cache->questionReward[$o->score] = new QuestionReward($o);
            unset($o->id);
            unset($o->score);
        }
    }

    /**
     * @return void
     */
    protected function initFixedTimeReward()
    {
        $this->cache->fixedTimeReward = [];
        $stmt = $this->pdo->query('SELECT * FROM `FixedTimeReward`');
        $rewards = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `FixedTimeRewardSource`');
        $sources = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($rewards as $o) {
            $data[$o->id] = $o;
        }

        foreach ($sources as $o) {
            $data[$o->ftrid]->source[$o->eid] = $o->num;
        }

        foreach ($data as $o) {
            $this->cache->fixedTimeReward[$o->id] = new FixedTimeReward($o);
        }
    }

    /**
     * @return void
     */
    protected function initRechargeReward()
    {
        $this->cache->rechargeReward = [];
        $stmt = $this->pdo->query('SELECT * FROM `RechargeReward`');
        $rewards = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `RechargeRewardSource`');
        $sources = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($rewards as $o) {
            $data[$o->id] = $o;
        }

        foreach ($sources as $o) {
            $data[$o->rrid]->source[$o->eid] = $o->num;
        }

        foreach ($data as $o) {
            $this->cache->rechargeReward[$o->id] = new RechargeReward($o);
        }
    }

    /**
     * @return void
     */
    protected function initActIns()
    {
        $stmt = $this->pdo->query("SELECT * FROM `ActIns`");
        $ins = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `ActInsFloor`');
        $floors = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `ActInsFloorNpc`');
        $npc = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `ActInsFloorDrop`');
        $drop = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($ins as $o) {
            $data[$o->id] = $o;
        }

        foreach ($floors as $o) {
            $data[$o->aid]->floors[$o->fid] = $o;
            unset($o->aid);
        }

        foreach ($npc as $o) {
            $data[$o->aid]->floors[$o->fid]->npc[$o->pos] = $o;
            unset($o->aid);
            unset($o->fid);
            unset($o->pos);
        }

        foreach ($drop as $o) {
            $data[$o->aid]->floors[$o->fid]->drop[$o->eid] = $o->num;
        }

        foreach ($data as $o) {
            if (false === isset($o->floors)) {
                continue;
            }
            $floors = $o->floors;
            unset($o->floors);
            foreach ($floors as $floor) {
                $o->floors[$floor->fid] = new ActInsFloor($floor);
            }
        }

        $this->cache->actins = [];
        foreach ($data as $o) {
            $this->cache->actins[$o->id] = new ActIns($o);
        }
    }

    /**
     * @return void
     */
    protected function initGift()
    {
        $stmt = $this->pdo->query('SELECT * FROM `Gift`');
        $gifts = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `GiftSource`');
        $source = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($gifts as $o) {
            $data[$o->id] = $o;
        }

        foreach ($source as $o) {
            $data[$o->gid]->source[$o->k] = $o->v;
        }

        $this->cache->gift = [];
        foreach ($data as $o) {
            $this->cache->gift[$o->id] = new Gift($o);
        }
    }

    /**
     * @return void
     */
    protected function initLeader()
    {
        $stmt = $this->pdo->query('SELECT * FROM `Leader`');
        $leaders = $stmt->fetchAll(PDO::FETCH_OBJ);

        $this->cache->leader = [];
        foreach ($leaders as $o) {
            $this->cache->leader[$o->sex][$o->part][$o->val] = $o->vip;
        }
    }

    /**
     *
     * @return void
     */
    protected function initResourceRecruit()
    {
        /* rr */
        $stmt = $this->pdo->query('SELECT * FROM `ResourceRecruit` ORDER BY `total` desc');
        $sets = $stmt->fetchAll(PDO::FETCH_OBJ);

        $this->cache->ResourceRecruit = [];
        foreach ($sets as $o) {
            $this->cache->ResourceRecruit[] = new ResourceRecruit($o);
        }

        /* rrp */
        $stmt = $this->pdo->query('SELECT * FROM `ResourceRecruitPercentage`');
        $sets = $stmt->fetchAll(PDO::FETCH_OBJ);

        $x = array(
            'soldier' => 'a400',
            'weapon'  => 'a200',
            'armor'   => 'a300',
            'horse'   => 'a100',
        );

        $this->cache->ResourceRecruitPercentage = [];
        foreach ($sets as $o) {
            foreach ($x as $k => $v) {
                if ($o->$v <= 0) {
                    continue;
                }

                ${$k}[] = (object) array('total' => $o->$k, 'prob' => $o->$v, 'army' => $v);
            }
        }

        foreach ($x as $k => $v) {
            usort($$k, function($a, $b) {
                return $b->total - $a->total;
            });

            $this->cache->ResourceRecruitPercentage[$k] = $$k;
        }

        /* rrg */
        $stmt = $this->pdo->query('SELECT * FROM `ResourceRecruitGroup`');
        $sets = $stmt->fetchAll(PDO::FETCH_OBJ);

        $this->cache->ResourceRecruitGroup = [];
        foreach ($sets as $o) {
            if ($o->prob > 0) {
                $this->cache->ResourceRecruitGroup[$o->gid][$o->eid] = $o->prob;
            }
        }
    }

    /**
     * @return void
     */
    protected function initNewzoneMission()
    {
        $stmt = $this->pdo->query('SELECT * FROM `NewzoneMission`');
        $missions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `NewzoneMissionReward`');
        $rewards = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($missions as $mission) {
            $data[$mission->id] = $mission;
        }

        foreach ($rewards as $reward) {
            $data[$reward->id]->rewards[$reward->k] = $reward->v;
        }

        $this->cache->newzoneMission = [];
        foreach ($data as $o) {
            $this->cache->newzoneMission[$o->id] = new NewzoneMission($o);
        }
    }

    /**
     * @return void
     */
    protected function initNewzoneMissionIndexByType()
    {
        $this->cache->newzoneMissionIndexByType = [];
        foreach ($this->cache->newzoneMission as $o) {
            $this->cache->newzoneMissionIndexByType[$o->type][] = $o;
        }
    }

    /**
     * @return void
     */
    protected function initWeekMissionTypeIndexByFlag()
    {
        $stmt = $this->pdo->query('SELECT * FROM `WeekMissionType`');
        $types = $stmt->fetchAll(PDO::FETCH_OBJ);

        $this->cache->weekMissionTypeIndexByFlag = [];
        foreach ($types as $o) {
            $this->cache->weekMissionTypeIndexByFlag[$o->flag][] = $o;
        }
    }

    /**
     * @return void
     */
    protected function initWeekMission()
    {
        $stmt = $this->pdo->query('SELECT * FROM `WeekMission`');
        $missions = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `WeekMissionReward`');
        $rewards = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($missions as $mission) {
            $data[$mission->id] = $mission;
        }

        foreach ($rewards as $reward) {
            $data[$reward->id]->rewards[$reward->k] = $reward->v;
        }

        $this->cache->weekMission = [];
        foreach ($data as $o) {
            $this->cache->weekMission[$o->id] = new WeekMission($o);
        }
    }

    /**
     * @return void
     */
    protected function initWeekMissionIndexByType()
    {
        $this->cache->weekMissionIndexByType = [];
        foreach ($this->cache->weekMission as $o) {
            $this->cache->weekMissionIndexByType[$o->type][] = $o;
        }
    }

    /**
     * @return void
     */
    protected function initPropGoods()
    {
        $stmt = $this->pdo->query('SELECT * FROM `PropGoods`');
        $goods = $stmt->fetchAll(PDO::FETCH_OBJ);

        $this->cache->propGoods = [];
        foreach ($goods as $o) {
            $this->cache->propGoods[$o->id] = new PropGoods($o);
        }
    }

    /**
     * @return void
     */
    protected function initDayIns()
    {
        $stmt = $this->pdo->query('SELECT * FROM `DayIns`');
        $ins = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `DayInsDiff`');
        $diff = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `DayInsDrop`');
        $drop = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `DayInsNPC`');
        $npc  = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($ins as $o) {
            $data[$o->id] = $o;
        }

        foreach ($diff as $o) {
            $data[$o->iid]->diff[$o->diff] = $o;
            unset($o->iid);
        }

        foreach ($drop as $o) {
            $data[$o->iid]->diff[$o->diff]->drop[$o->k] = $o->v;
        }

        foreach ($npc as $o) {
            $data[$o->iid]->diff[$o->diff]->npc[$o->pos] = $o;
            unset($o->iid);
            unset($o->diff);
            unset($o->pos);
        }

        $this->cache->dayins = [];
        foreach ($data as $ins) {
            foreach ($ins->diff as $diff => $o) {
                $ins->diff[$diff] = new DayInsDiff($o);
            }
        }
        foreach ($data as $o) {
            $this->cache->dayins[$o->id] = new DayIns($o);
        }
    }

    /**
     * @return void
     */
    protected function initWorldBoss()
    {
        $stmt = $this->pdo->query('SELECT * FROM `WorldBoss`');
        $boss = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `WorldBossDrop`');
        $rewards = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($boss as $o) {
            $data[$o->id] = $o;
        }
        foreach ($rewards as $o) {
            $data[$o->bid]->drop[$o->k] = $o->v;
        }

        $this->cache->worldboss = [];
        foreach ($boss as $o) {
            $this->cache->worldboss[$o->id] = new WorldBoss($o);
        }
    }

    /**
     * @return void
     */
    protected function initGuild()
    {
        $stmt = $this->pdo->query('SELECT * FROM `GuildContribution`');
        $guildContribution = $stmt->fetchAll(PDO::FETCH_OBJ);
        $this->cache->guildContribution = [];
        foreach ($guildContribution as $o) {
            $this->cache->guildContribution[$o->id] = new Contribution($o);
        }


        $stmt = $this->pdo->query('SELECT * FROM `GuildExp`');
        $guildexp = $stmt->fetchAll(PDO::FETCH_OBJ);
        $this->cache->guildexp = [];
        foreach ($guildexp as $o) {
            $this->cache->guildexp[$o->lvl] = new GuildExp($o);
        }

        $stmt = $this->pdo->query('SELECT * FROM `GuildGift`');
        $guildGift = $stmt->fetchAll(PDO::FETCH_OBJ);
        $stmt = $this->pdo->query('SELECT * FROM `GuildGiftSource`');
        $source = $stmt->fetchAll(PDO::FETCH_OBJ);
        $data = [];
        foreach ($guildGift as $o) {
            $data[$o->id] = $o;
        }
        foreach ($source as $o) {
            $data[$o->id]->source[$o->k] = $o->v;
        }
        $this->cache->guildGift = [];
        foreach ($data as $o) {
            $this->cache->guildGift[$o->id] = new GuildGift($o);
        }

        $stmt = $this->pdo->query('SELECT * FROM `GuildChest`');
        $guildChest = $stmt->fetchAll(PDO::FETCH_OBJ);
        $stmt = $this->pdo->query('SELECT * FROM `GuildChestSource`');
        $source = $stmt->fetchAll(PDO::FETCH_OBJ);
        $data = [];
        foreach ($guildChest as $o) {
            $data[$o->id] = $o;
        }
        foreach ($source as $o) {
            $data[$o->id]->source[$o->k] = $o->v;
        }
        $this->cache->guildChest = [];
        foreach ($guildChest as $o) {
            $this->cache->guildChest[$o->id] = new GuildChest($o);
        }
    }

    /**
     * @return void
     */
    protected function initMessageTemplate()
    {
        $stmt = $this->pdo->query('SELECT * FROM `MessageTemplate`');
        $templates = $stmt->fetchAll(PDO::FETCH_OBJ);
        $this->cache->messageTemplate = [];
        foreach ($templates as $o) {
            $this->cache->messageTemplate[$o->type] = new MessageTemplate($o);
        }
    }

    /**
     * @return void
     */
    protected function initActivity()
    {
        $stmt = $this->pdo->query('SELECT * FROM `ActDiaRec`');
        $diarec = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $stmt = $this->pdo->query('SELECT * FROM `ActDiaRecSource`');
        $source = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($diarec as $o) {
            $data[$o->id] = $o;
        }

        foreach ($source as $o) {
            $data[$o->id]->rewards[$o->k] = $o->v;
        }
        $this->cache->actdiarec = [];
        foreach ($data as $o) {
            $this->cache->actdiarec[$o->id] = new DiaRec($o);
        }
    }

	/**
	 * @return void
	 */
	protected function initBond()
	{
		$stmt = $this->pdo->query('SELECT * FROM `Bond`');
		$bonds = $stmt->fetchAll(PDO::FETCH_OBJ);

		$stmt = $this->pdo->query('SELECT * FROM `BondEffect`');
		$effects = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$stmt = $this->pdo->query('SELECT * FROM `BondMember`');
		$members = $stmt->fetchAll(PDO::FETCH_OBJ);

		$data = [];
		foreach ($bonds as $o) {
			$data[$o->id] = $o;
			unset($o->tag);
		}
		foreach ($effects as $o) {
			$data[$o->bid]->effects[$o->type] = $o->value;
		}
		foreach ($members as $o) {
			$data[$o->bid]->members[] = $o->eid;
		}

		$this->cache->bond = [];
		foreach ($data as $o) {
			$this->cache->bond[$o->id] = $o;
		}
	}

    /**
     * @return void
     */
    protected function initCompletionReward()
    {
        $stmt = $this->pdo->query('SELECT * FROM `CompletionReward`');
        $rewards = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stmt = $this->pdo->query('SELECT * FROM `CompletionRewardSource`');
        $source = $stmt->fetchAll(PDO::FETCH_OBJ);

        $data = [];
        foreach ($rewards as $o) {
            $data[$o->type][$o->idx] = $o;
            unset($o->type);
        }

        foreach ($source as $o) {
            $data[$o->type][$o->idx]->reward[$o->k] = $o->v;
        }

        $this->cache->completionReward = $data;
        foreach ($this->cache->completionReward as $type => $rewards) {
            foreach ($rewards as $o) {
                $this->cache->completionReward[$type][$o->idx] = new CompletionReward($o);
            }
        }
    }

    /**
     * @param  string  $table
     * @param  integer $id
     * @return void
     */
    public function debug($table = null, $id = null)
    {
        if ($table === null) {
            print_r($this->cache);
            return;
        }

        if ($id === null) {
            if (isset($this->cache->$table)) {
                print_r($this->cache->$table);
            } else {
                var_dump(null);
            }
            return;
        }

        if (isset($this->cache->$table)) {
            $table = $this->cache->$table;
            if (isset($table[$id])) {
                print_r($table[$id]);
            } else {
                var_dump(null);
            }
        } else {
            var_dump(null);
        }
    }
}

/**
 *
 */
$builder = new CacheBuilder($boot->globals->pdo);
$builder->setDestination(APPDIR.'/cache/config.data');
if ($builder->export()) {
    $boot->log(1, "build cache file %s, success!", $builder->getDestination());
} else {
    $boot->log(1, "build cache failure!");
}

$builder->filter(['merchandise']);
$builder->setDestination(APPDIR.'/cache/merchandise.config.data');
if ($builder->export()) {
    $boot->log(1, "build cache file %s, success!", $builder->getDestination());
} else {
    $boot->log(1, "build cache failure!");
}
