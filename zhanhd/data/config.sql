####################################################################################################

--
-- `zhanhd.config`.`Building`
--
drop table if exists `zhanhd.config`.`Building`;
create table `zhanhd.config`.`Building` (
    `id` int unsigned not null auto_increment primary key,
    `tag` char(15) not null unique,
    `enable` tinyint unsigned not null,
    `collectable` tinyint unsigned not null
) Engine = MyISAM;

--
-- `zhanhd.config`.`BuildingProperty`
--
drop table if exists `zhanhd.config`.`BuildingProperty`;
create table `zhanhd.config`.`BuildingProperty` (
    `bid` int unsigned not null,
    `lvl` tinyint unsigned not null,

    `ugdur` int unsigned not null,

    `ctmin` int unsigned not null,
    `ctmax` int unsigned not null,

    `cost` int unsigned not null,

    primary key (`bid`, `lvl`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`BuildingUpgradation`
--
drop table if exists `zhanhd.config`.`BuildingUpgradation`;
create table `zhanhd.config`.`BuildingUpgradation` (
    `bid` int unsigned not null,
    `lvl` tinyint unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`bid`, `lvl`, `k`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`BuildingProduction`
--
drop table if exists `zhanhd.config`.`BuildingProduction`;
create table `zhanhd.config`.`BuildingProduction` (
    `bid` int unsigned not null,
    `lvl` tinyint unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`bid`, `lvl`, `k`)
) Engine = MyISAM;

####################################################################################################

--
-- `zhanhd.config`.`Entity`
--
drop table if exists `zhanhd.config`.`Entity`;
create table `zhanhd.config`.`Entity` (
    `id` int unsigned not null primary key,
    `tag` char(15) not null,

    `type` int unsigned not null,
    `cost` int unsigned not null,

    `rarity` tinyint unsigned not null,
    `lvlreq` tinyint unsigned not null,

    `salable` tinyint unsigned not null,
    `diffrace` tinyint unsigned not null,
    `stackable` tinyint unsigned not null
) Engine = MyISAM;

--
-- `zhanhd.config`.`EntityProperty`
--
drop table if exists `zhanhd.config`.`EntityProperty`;
create table `zhanhd.config`.`EntityProperty` (
    `eid` int unsigned not null,

    `k` char(15) not null,
    `v` varchar(32) not null,

    primary key (`eid`, `k`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`EntityDecomposed`
--
drop table if exists `zhanhd.config`.`EntityDecomposed`;
create table `zhanhd.config`.`EntityDecomposed` (
    `eid` int unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`eid`, `k`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`EntityDrop`
--
drop table if exists `zhanhd.config`.`EntityDrop`;
create table `zhanhd.config`.`EntityDrop` (
    `eid` int unsigned not null,
    `k` int unsigned not null,
    `v` int unsigned not null,
    `prob` int unsigned not null,
    primary key (`eid`, `k`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`EntityExperience`
--
drop table if exists `zhanhd.config`.`EntityExperience`;
create table `zhanhd.config`.`EntityExperience` (
    `lvl` int unsigned not null,
    `type` int unsigned not null,

    `exp` int unsigned not null,

    primary key (`lvl`, `type`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`EntityEnhance`
--
drop table if exists `zhanhd.config`.`EntityEnhance`;
create table `zhanhd.config`.`EntityEnhance` (
    `lvl` int unsigned not null,
    `exp` int unsigned not null,

    `str` int unsigned not null,
    `int` int unsigned not null,
    `stm` int unsigned not null,
    `dex` int unsigned not null,

    `dmg` int unsigned not null,
    `def` int unsigned not null,
    `hpt` int unsigned not null,

    `slvl` int unsigned not null,
    primary key (`lvl`)
) Engine = MyISAM;

##################################################

--
-- `zhanhd.config`.`EntityArmy`
--
drop table if exists `zhanhd.config`.`EntityArmy`;
create table `zhanhd.config`.`EntityArmy` (
    `eid` int unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`eid`, `k`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`EntitySkill`
--
drop table if exists `zhanhd.config`.`EntitySkill`;
create table `zhanhd.config`.`EntitySkill` (
    `eid` int unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`eid`, `k`)
) Engine = MyISAM;

####################################################################################################

--
-- `zhanhd.config`.`Army`
--
drop table if exists `zhanhd.config`.`Army`;
create table `zhanhd.config`.`Army` (
    `id` int unsigned not null primary key,
    `tag` char(15) not null unique,

    `type` smallint unsigned not null,
    `dex` smallint unsigned not null,

    `dmg` int unsigned not null,
    `dmgperlvl` int unsigned not null,

    `def` int unsigned not null,
    `defperlvl` int unsigned not null,

    `hpt` int unsigned not null,
    `hptperlvl` int unsigned not null
) Engine = MyISAM;

--
-- `zhanhd.config`.`ArmyUpgradation`
--
drop table if exists `zhanhd.config`.`ArmyUpgradation`;
create table `zhanhd.config`.`ArmyUpgradation` (
    `aid` int unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`aid`, `k`)
) Engine = MyISAM;

##################################################

--
-- `zhanhd.config`.`EntityUseRule`
--
drop table if exists `zhanhd.config`.`EntityUseRule`;
create table `zhanhd.config`.`EntityUseRule` (
    `eid` int unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`eid`, `k`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`EntityEquipEffect`
--
drop table if exists `zhanhd.config`.`EntityEquipEffect`;
create table `zhanhd.config`.`EntityEquipEffect` (
    `eid` int unsigned not null,
    `lvl` tinyint unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`eid`, `k`)
) Engine = MyISAM;

##################################################

--
-- `zhanhd.config`.`EntityGroup`
--
drop table if exists `zhanhd.config`.`EntityGroup`;
create table `zhanhd.config`.`EntityGroup` (
    `gid` int unsigned not null,
    `eid` int unsigned not null,
    `prob` int unsigned not null,
    primary key (`gid`, `eid`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`EntityPicked`
--
drop table if exists `zhanhd.config`.`EntityPicked`;
create table `zhanhd.config`.`EntityPicked` (
    `id` int unsigned not null primary key,
    `tag` char(25) not null unique,
    `pick` tinyint not null,
    `void` smallint unsigned not null,
    `deep` smallint unsigned not null
) Engine = MyISAM;

--
-- `zhanhd.config`.`EntityPickedSource`
--
drop table if exists `zhanhd.config`.`EntityPickedSource`;
create table `zhanhd.config`.`EntityPickedSource` (
    `epid` int unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`epid`, `k`)
) Engine = MyISAM;

##################################################

--
-- `zhanhd.config`.`Forge`
--
drop table if exists `zhanhd.config`.`Forge`;
create table `zhanhd.config`.`Forge` (
    `id` int unsigned not null primary key,

    `prop` int unsigned not null,
    `gold` int unsigned not null
) Engine = MyISAM;

##################################################

--
-- `zhanhd.config`.`Goods`
--
drop table if exists `zhanhd.config`.`Goods`;
create table `zhanhd.config`.`Goods` (
    `id` int unsigned not null primary key,
    `tag` char(15) not null unique,

    `epid` int unsigned not null,
    `gold` int unsigned not null,

    `enable` tinyint unsigned not null
) Engine = MyISAM;

--
-- `zhanhd.config`.`GoodsRequirement`
--
drop table if exists `zhanhd.config`.`GoodsRequirement`;
create table `zhanhd.config`.`GoodsRequirement` (
    `gid` int unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`gid`, `k`)
) Engine = MyISAM;

####################################################################################################

--
-- `zhanhd.config`.`Skill`
--
drop table if exists `zhanhd.config`.`Skill`;
create table `zhanhd.config`.`Skill` (
    `id` int unsigned not null primary key,
    `tag` char(15) not null,

    `type` smallint unsigned not null,
    `mode` char(15) not null,
    `nums` smallint unsigned not null,
    `once` tinyint unsigned not null,

    `v1`  smallint unsigned not null,
    `v2`  smallint unsigned not null,
    `v3`  smallint unsigned not null,
    `v4`  smallint unsigned not null,
    `v5`  smallint unsigned not null,
    `sida` smallint unsigned not null,
    `syma` smallint unsigned not null,
    `sidb` smallint unsigned not null,
    `symb` smallint unsigned not null
) Engine = MyISAM;

--
-- `zhanhd.config`.`Effect`
--
drop table if exists `zhanhd.config`.`Effect`;
create table `zhanhd.config`.`Effect` (
    `id` int unsigned not null primary key,
    `tag` char(15) not null unique,
    `code` char(15) not null,
    `gid` int unsigned not null,
    `runtime` int unsigned not null
) Engine = MyISAM;

--
-- `zhanhd.config`.`SkillEffect`
--
drop table if exists `zhanhd.config`.`SkillEffect`;
create table `zhanhd.config`.`SkillEffect` (
    `sid` int unsigned not null,
    `eid` int unsigned not null,

    `at` tinyint unsigned not null,
    `op` tinyint unsigned not null,
    `to` tinyint unsigned not null,

    `anti`     tinyint unsigned not null,
    `army`     smallint unsigned not null,
    `dynasty`  smallint unsigned not null,
    `diffrace` tinyint unsigned not null,

    `v0`  smallint unsigned not null,
    `v1`  smallint unsigned not null,
    `v2`  smallint unsigned not null,
    `v3`  smallint unsigned not null,
    `v4`  smallint unsigned not null,
    `v5`  smallint unsigned not null,

    primary key (`sid`, `eid`)
) Engine = MyISAM;

##################################################

--
-- `zhanhd.config`.`Formation`
--
drop table if exists `zhanhd.config`.`Formation`;
create table `zhanhd.config`.`Formation` (
    `id` int unsigned not null primary key,
    `tag` char(15) not null unique,

    `intreq` int unsigned not null
) Engine = MyISAM;

--
-- `zhanhd.config`.`FormationRestraint`
--
drop table if exists `zhanhd.config`.`FormationRestraint`;
create table `zhanhd.config`.`FormationRestraint` (
    `fid` int unsigned not null,
    `rid` int unsigned not null,

    primary key (`fid`, `rid`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`FormationAddition`
--
drop table if exists `zhanhd.config`.`FormationAddition`;
create table `zhanhd.config`.`FormationAddition` (
    `fid` int unsigned not null,
    `aid` int unsigned not null,
    `eid` int unsigned not null,

    `v` int unsigned not null,

    primary key (`fid`, `aid`, `eid`)
) Engine = MyISAM;

####################################################################################################

--
-- `zhanhd.config`.`Instance`
--
drop table if exists `zhanhd.config`.`Instance`;
create table `zhanhd.config`.`Instance` (
    `diff` int unsigned not null,
    `id`  int unsigned not null,
    `prev` int unsigned not null,
    `next` int unsigned not null,
    `energy` int unsigned not null,

    primary key (`diff`, `id`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`InsEvt`;
--
drop table if exists `zhanhd.config`.`InsEvt`;
create table `zhanhd.config`.`InsEvt` (
    `diff`  int unsigned not null,
    `iid`   int unsigned not null,
    `evt`   int unsigned not null,
    `flags` int unsigned not null,
    `fmt`   int unsigned not null,
    `exp`   int unsigned not null,
    primary key (`diff`, `iid`, `evt`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`InsEvtPrev`
--
drop table if exists `zhanhd.config`.`InsEvtPrev`;
create table `zhanhd.config`.`InsEvtPrev` (
    `diff` int unsigned not null,
    `iid`  int unsigned not null,
    `evt`  int unsigned not null,
    `k`    int unsigned not null,
    primary key (`diff`, `iid`, `evt`, `k`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`InsEvtNext`
--
drop table if exists `zhanhd.config`.`InsEvtNext`;
create table `zhanhd.config`.`InsEvtNext` (
    `diff` int unsigned not null,
    `iid`  int unsigned not null,
    `evt`  int unsigned not null,
    `k`    int unsigned not null,
    `v`    int unsigned not null,
    primary key (`diff`, `iid`, `evt`, `k`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`InsEvtNpc`
--
drop table if exists `zhanhd.config`.`InsEvtNpc`;
create table `zhanhd.config`.`InsEvtNpc` (
    `diff` int unsigned not null,
    `iid`  int unsigned not null,
    `evt`  int unsigned not null,

    `pos` int unsigned not null,
    `eid` int unsigned not null,
    `lvl` int unsigned not null,
    `ehc` int unsigned not null,

    primary key (`diff`, `iid`, `evt`, `pos`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`InsEvtDrop`
--
drop table if exists `zhanhd.config`.`InsEvtDrop`;
create table `zhanhd.config`.`InsEvtDrop` (
    `diff` int unsigned not null,
    `iid`  int unsigned not null,
    `evt`  int unsigned not null,

    `index` int unsigned not null,
    `first` int unsigned not null,
    `prob`  int unsigned not null,
    primary key (`diff`, `iid`, `evt`, `index`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`InsEvtDropSource`
--
drop table if exists `zhanhd.config`.`InsEvtDropSource`;
create table `zhanhd.config`.`InsEvtDropSource` (
    `diff` int unsigned not null,
    `iid`  int unsigned not null,
    `evt`  int unsigned not null,

    `index` int unsigned not null,
    `eid`   int unsigned not null,
    `num`   int unsigned not null,
    primary key (`diff`, `iid`, `evt`, `index`, `eid`)
) Engine = MyISAM;

####################################################################################################

--
-- `zhanhd.config`.`Battle`
--
drop table if exists `zhanhd.config`.`Battle`;
create table `zhanhd.config`.`Battle` (
    `id` int unsigned not null,
    `diff` int unsigned not null,
    `exp` int unsigned not null,
    `power` int unsigned not null,
    primary key (`id`, `diff`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`BattleReward`;
create table `zhanhd.config`.`BattleReward` (
    `bid`   int unsigned not null,
    `diff`  int unsigned not null,
    `index` int unsigned not null,
    `prob`  int unsigned not null,
    `eid`   int unsigned not null,
    `num`   int unsigned not null,
    primary key (`bid`, `diff`, `index`)
) Engine = MyISAM;

####################################################################################################

--
-- `zhanhd.config`.`Crusade`
--
drop table if exists `zhanhd.config`.`Crusade`;
create table `zhanhd.config`.`Crusade` (
    `id` int unsigned not null primary key,

    `act` int unsigned not null,
    `seq` int unsigned not null,

    `caplvl` int unsigned not null,
    `sumlvl` int unsigned not null,
    `sucrto` int unsigned not null,

    `duration` int unsigned not null,
    `times`    int unsigned not null,
    `exp`      int unsigned not null,
    `void`     int unsigned not null
) Engine = MyISAM;

--
-- `zhanhd.config`.`CrusadeResource`
--
drop table if exists `zhanhd.config`.`CrusadeResource`;
create table `zhanhd.config`.`CrusadeResource` (
    `cid` int unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`cid`, `k`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`CrusadeSource`
--
drop table if exists `zhanhd.config`.`CrusadeSource`;
create table `zhanhd.config`.`CrusadeSource` (
    `cid` int unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`cid`, `k`)
) Engine = MyISAM;

####################################################################################################

--
-- `zhanhd.config`.`Achievement`
--
drop table if exists `zhanhd.config`.`Achievement`;
create table `zhanhd.config`.`Achievement` (
    `id` int unsigned not null primary key,
    `tag` char(15) not null unique,

    `type` tinyint unsigned not null,
    `cmd`  char(25) not null,
    `argv` char(50) not null,

    `intval` int unsigned not null,
    `strval` char(50) not null,
    `unlock` char(25) not null
) Engine = MyISAM;

--
-- `zhanhd.config`.`AchievementSource`
--
drop table if exists `zhanhd.config`.`AchievementSource`;
create table `zhanhd.config`.`AchievementSource` (
    `aid` int unsigned not null,

    `k` int unsigned not null,
    `v` int unsigned not null,

    primary key (`aid`, `k`)
) Engine = MyISAM;

####################################################################################################

--
-- `zhanhd.config`.`Error`
--
drop table if exists `zhanhd.config`.`Error`;
create table `zhanhd.config`.`Error` (
    `code`  int unsigned not null auto_increment,
    `error` varchar(100) not null,
    `info`  varchar(256) not null,
    primary key (`code`)
) Engine = MyISAM;

####################################################################################################

--
-- `zhanhd.config`.`NPCLineup`
--
drop table if exists `zhanhd.config`.`NPCLineup`;
create table `zhanhd.config`.`NPCLineup` (
    `id` int unsigned not null,
    `fid` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`NPCLineupHero`
--
drop table if exists `zhanhd.config`.`NPCLineupHero`;
create table `zhanhd.config`.`NPCLineupHero` (
    `lid`   int unsigned not null,
    `pos`  tinyint unsigned not null,
    `eid`  int unsigned not null,
    `lvl`  tinyint unsigned not null,
    `ehc` tinyint unsigned not null,
    primary key (`lid`, `pos`)
) Engine = MyISAM;

####################################################################################################

--
-- `zhanhd.config`.`Drop`
--
drop table if exists `zhanhd.config`.`Drop`;
create table `zhanhd.config`.`Drop` (
    `id`    int unsigned not null,
    `index` tinyint unsigned not null,
    `type`  tinyint unsigned not null,
    `prob`  tinyint unsigned not null,
    primary key (`id`, `index`)
) Engine = MyISAM;

--
-- `zhanhd.config`.`DropSource`
--
drop table if exists `zhanhd.config`.`DropSource`;
create table `zhanhd.config`.`DropSource` (
    `id` int unsigned not null,
    `index` tinyint unsigned not null,
    `k` int unsigned not null,
    `v` int unsigned not null,
    primary key (`id`, `index`, `k`)
) Engine = MyISAM;

####################################################################################################

--
-- `zhanhd.config`.`Activity`
--
drop table if exists `zhanhd.config`.`Activity`;
create table `zhanhd.config`.`Activity` (
    `id`   int unsigned not null,
    `type` tinyint unsigned not null,
    `tid`  int unsigned not null,
    `scoreIncr` int unsigned not null,
    `rid` int unsigned not null,

    primary key (`id`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`Reward`;
create table `zhanhd.config`.`Reward` (
    `id` int unsigned not null,
    `tag` varchar(24) not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`RewardRank`;
create table `zhanhd.config`.`RewardRank` (
    `rid` int unsigned not null,
    `index` int unsigned not null,
    `lowRank` int unsigned not null,
    primary key (`rid`, `index`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`RewardRankSource`;
create table `zhanhd.config`.`RewardRankSource` (
    `rid` int unsigned not null,
    `index` int unsigned not null,
    `k` int unsigned not null,
    `v` int unsigned not null,
    primary key (`rid`, `index`, `k`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`RewardRankCoherence`;
create table `zhanhd.config`.`RewardRankCoherence` (
    `rid` int unsigned not null,
    `index` int unsigned not null,
    `k` varchar(32) not null,
    `v` int unsigned not null,
    primary key (`rid`, `index`, `k`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`RewardScore`;
create table `zhanhd.config`.`RewardScore` (
    `rid` int unsigned not null,
    `index` int unsigned not null,
    `require` int unsigned not null,
    primary key (`rid`, `index`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`RewardScoreSource`;
create table `zhanhd.config`.`RewardScoreSource` (
    `rid` int unsigned not null,
    `index` int unsigned not null,
    `k` int unsigned not null,
    `v` int unsigned not null,
    primary key (`rid`, `index`, `k`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`SigninReward`;
create table `zhanhd.config`.`SigninReward` (
    `dom` tinyint unsigned not null,
    `eid` int unsigned not null,
    `num` int unsigned not null,
    `vipnum` int unsigned not null,
    primary key (`dom`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`Question`;
create table `zhanhd.config`.`Question` (
    `id` smallint unsigned not null,
    `answer` tinyint not null,

    primary key (`id`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`Merchandise`;
create table `zhanhd.config`.`Merchandise` (
    `id` smallint unsigned not null,
    `eid` int unsigned not null,
    `num` int unsigned not null,
    `price` int unsigned not null,
    primary key(`id`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`DepositReward`;
create table `zhanhd.config`.`DepositReward` (
    `id` smallint unsigned not null,
    `limit` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`DepositRewardSource`;
create table `zhanhd.config`.`DepositRewardSource` (
    `drid` smallint unsigned not null,
    `eid` int unsigned not null,
    `num` int unsigned not null,
    primary key (`drid`, `eid`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`InviteReward`;
create table `zhanhd.config`.`InviteReward` (
    `id` smallint unsigned not null,
    `limit` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`InviteRewardSource`;
create table `zhanhd.config`.`InviteRewardSource` (
    `irid` smallint unsigned not null,
    `eid` int unsigned not null,
    `num` int unsigned not null,
    primary key (`irid`, `eid`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`GreenerReward`;
create table `zhanhd.config`.`GreenerReward` (
    `day` tinyint unsigned not null,
    `eid` int unsigned not null,
    `num` int unsigned not null,
    primary key (`day`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`FriendShipGoods`;
create table `zhanhd.config`.`FriendShipGoods` (
    `id`    int unsigned not null,
    `eid`   int unsigned not null,
    `price` int unsigned not null,
    `prob`  int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`QuestionReward`;
create table `zhanhd.config`.`QuestionReward` (
    `id` int unsigned not null,
    `score` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`QuestionRewardSource`;
create table `zhanhd.config`.`QuestionRewardSource` (
    `qrid` int unsigned not null,
    `eid` int unsigned not null,
    `num` int unsigned not null,
    primary key (`qrid`, `eid`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`ActIns`;
create table `zhanhd.config`.`ActIns` (
    `id` int unsigned not null,
    `name`   varchar(32) not null,
    `npcnum` int unsigned not null,
    `armytp` int unsigned not null,
    `rarity` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`ActInsFloor`;
create table `zhanhd.config`.`ActInsFloor` (
    `aid` int unsigned not null,
    `fid` int unsigned not null,
    `fmt` int unsigned not null,
    primary key (`aid`, `fid`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`ActInsFloorNpc`;
create table `zhanhd.config`.`ActInsFloorNpc` (
    `aid` int unsigned not null,
    `fid` int unsigned not null,
    `pos` int unsigned not null,
    `eid` int unsigned not null,
    `lvl` int unsigned not null,
    `ehc` int unsigned not null,
    primary key (`aid`, `fid`, `pos`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`ActInsFloorDrop`;
create table `zhanhd.config`.`ActInsFloorDrop` (
    `aid` int unsigned not null,
    `fid` int unsigned not null,
    `eid` int unsigned not null,
    `num` int unsigned not null,
    primary key (`aid`, `fid`, `eid`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`Gift`;
create table `zhanhd.config`.`Gift` (
    `id` varchar(24) not null,
    `type` tinyint unsigned not null,
    `release` int unsigned not null,
    `expire`  int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`GiftSource`;
create table `zhanhd.config`.`GiftSource` (
    `gid` varchar(24) not null,
    `k` int unsigned not null,
    `v` int unsigned not null,
    primary key (`gid`, `k`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`Leader`;
create table `zhanhd.config`.`Leader` (
    `sex` tinyint unsigned not null,
    `part` varchar(12) not null,
    `val` tinyint unsigned not null,
    `vip` tinyint unsigned not null,
    primary key (`sex`, `part`, `val`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`ResourceRecruit`;
create table `zhanhd.config`.`ResourceRecruit` (
    `id` int unsigned not null auto_increment primary key,

    `total` int unsigned not null,
    `prop` int unsigned not null,
    `prob1` int unsigned not null,
    `prob2` int unsigned not null,
    `prob3` int unsigned not null,
    `prob4` int unsigned not null,
    `prob5` int unsigned not null
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`ResourceRecruitGroup`;
create table `zhanhd.config`.`ResourceRecruitGroup` (
    `gid` int unsigned not null,
    `eid` int unsigned not null,
    `prob` int unsigned not null,

    primary key (`gid`, `eid`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`ResourceRecruitPercentage`;
create table `zhanhd.config`.`ResourceRecruitPercentage` (
    `id` int unsigned not null auto_increment primary key,

    `weapon` int unsigned not null,
    `a200` int unsigned not null,
    `armor` int unsigned not null,
    `a300` int unsigned not null,
    `soldier` int unsigned not null,
    `a400` int unsigned not null,
    `horse` int unsigned not null,
    `a100` int unsigned not null
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`NewzoneMission`;
create table `zhanhd.config`.`NewzoneMission` (
    `id` int unsigned not null,
    `type` tinyint unsigned not null,
    `intval` int unsigned not null,
    `extra` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`NewzoneMissionReward`;
create table `zhanhd.config`.`NewzoneMissionReward` (
    `id` int unsigned not null,
    `k` int unsigned not null,
    `v` int unsigned not null,
    primary key (`id`, `k`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`WeekMissionType`;
create table `zhanhd.config`.`WeekMissionType` (
    `type` tinyint unsigned not null,
    `flag` tinyint unsigned not null,
    `pos`  tinyint unsigned not null,
    primary key (`type`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`WeekMission`;
create table `zhanhd.config`.`WeekMission` (
    `id` int unsigned not null,
    `type` tinyint unsigned not null,
    `intval` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`WeekMissionReward`;
create table `zhanhd.config`.`WeekMissionReward` (
    `id` int unsigned not null,
    `k` int unsigned not null,
    `v` int unsigned not null,
    primary key (`id`, `k`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`PropGoods`;
create table `zhanhd.config`.`PropGoods` (
    `id` int unsigned not null,
    `price` int unsigned not null,
    `incr` int unsigned not null,
    `eid` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`DayIns`;
create table `zhanhd.config`.`DayIns` (
    `id` int unsigned not null,
    `unlock` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`DayInsDiff`;
create table `zhanhd.config`.`DayInsDiff` (
    `iid` int unsigned not null,
    `diff` int unsigned not null,
    `rlvl` int unsigned not null,
    `fmt` int unsigned not null,
    `exp` int unsigned not null,
    primary key (`iid`, `diff`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`DayInsDrop`;
create table `zhanhd.config`.`DayInsDrop` (
    `iid` int unsigned not null,
    `diff` int unsigned not null,
    `k` int unsigned not null,
    `v` int unsigned not null,
    primary key (`iid`, `diff`, `k`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`DayInsNPC`;
create table `zhanhd.config`.`DayInsNPC` (
    `iid` int unsigned not null,
    `diff` int unsigned not null,
    `pos` int unsigned not null,
    `eid` int unsigned not null,
    `lvl` int unsigned not null,
    `ehc` int unsigned not null,
    primary key (`iid`, `diff`, `pos`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`WorldBoss`;
create table `zhanhd.config`.`WorldBoss` (
    `id`  int unsigned not null,
    `ehc` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`WorldBossDrop`;
create table `zhanhd.config`.`WorldBossDrop` (
    `bid` int unsigned not null,
    `k`   int unsigned not null,
    `v`   int unsigned not null,
    primary key (`bid`, `k`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`GuildContribution`;
create table `zhanhd.config`.`GuildContribution` (
    `id`  int unsigned not null,
    `eid` int unsigned not null,
    `num` int unsigned not null,
    `contribution` int unsigned not null,
    `friendship` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`GuildExp`;
create table `zhanhd.config`.`GuildExp` (
    `lvl` int unsigned not null,
    `exp` int unsigned not null,
    primary key (`lvl`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`GuildGift`;
create table `zhanhd.config`.`GuildGift` (
    `id`  int unsigned not null,
    `lvl` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`GuildGiftSource`;
create table `zhanhd.config`.`GuildGiftSource` (
    `id` int unsigned not null,
    `k` int unsigned not null,
    `v` int unsigned not null,
    primary key (`id`, `k`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`GuildChest`;
create table `zhanhd.config`.`GuildChest` (
    `id` int unsigned not null,
    `score` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`GuildChestSource`;
create table `zhanhd.config`.`GuildChestSource` (
    `id` int unsigned not null,
    `k` int unsigned not null,
    `v` int unsigned not null,
    primary key (`id`, `k`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`FixedTimeReward`;
create table `zhanhd.config`.`FixedTimeReward` (
    `id` int unsigned not null,
    `sec` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`FixedTimeRewardSource`;
create table `zhanhd.config`.`FixedTimeRewardSource` (
    `ftrid` int unsigned not null,
    `eid` int unsigned not null,
    `num` int unsigned not null,
    primary key (`ftrid`, `eid`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`RechargeReward`;
create table `zhanhd.config`.`RechargeReward` (
    `id` int unsigned not null,
    `times` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`RechargeRewardSource`;
create table `zhanhd.config`.`RechargeRewardSource` (
    `rrid` int unsigned not null,
    `eid` int unsigned not null,
    `num` int unsigned not null,
    primary key (`rrid`, `eid`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`MessageTemplate`;
create table `zhanhd.config`.`MessageTemplate` (
    `type` int unsigned not null,
    `title` varchar(48) not null,
    `content` varchar(256) not null,
    primary key (`type`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`ActDiaRec`;
create table `zhanhd.config`.`ActDiaRec` (
    `id` int unsigned not null,
    `times` int unsigned not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`ActDiaRecSource`;
create table `zhanhd.config`.`ActDiaRecSource` (
    `id` int unsigned not null,
    `k`  int unsigned not null,
    `v`  int unsigned not null,
    primary key (`id`, `k`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`Bond`;
create table `zhanhd.config`.`Bond` (
    `id` int unsigned not null,
    `tag` varchar(24) not null,
    primary key (`id`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`BondEffect`;
create table `zhanhd.config`.`BondEffect` (
    `bid` int unsigned not null,
    `type` tinyint unsigned not null,
    `value` tinyint unsigned not null,
    primary key (`bid`, `type`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`BondMember`;
create table `zhanhd.config`.`BondMember` (
    `bid` int unsigned not null,
    `eid` int unsigned not null,
    primary key (`bid`, `eid`)
) Engine = MyISAM;

####################################################################################################

drop table if exists `zhanhd.config`.`CompletionReward`;
create table `zhanhd.config`.`CompletionReward` (
    `type` tinyint unsigned not null,
    `idx` tinyint unsigned not null,
    `completion` int unsigned not null,
    primary key (`type`, `idx`)
) Engine = MyISAM;

drop table if exists `zhanhd.config`.`CompletionRewardSource`;
create table `zhanhd.config`.`CompletionRewardSource` (
    `type` tinyint unsigned not null,
    `idx` int unsigned not null,
    `k` int unsigned not null,
    `v` int unsigned not null,
    primary key (`type`, `idx`, `k`)
) Engine = MyISAM;