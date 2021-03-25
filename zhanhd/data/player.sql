####################################################################################################

--
-- `zhanhd.player`.`Player`
--
drop table if exists `zhanhd.player`.`Player`;
create table `zhanhd.player`.`Player` (
    `id` bigint unsigned not null primary key,
    `uid` bigint unsigned not null,
    `zone` smallint unsigned not null,
    `name` varchar(15) binary not null,
    `gold` int unsigned not null,
    `deposit` int unsigned not null,
    `created` bigint unsigned not null,
    `lastLogin` bigint unsigned not null,
    `logout` bigint unsigned not null,
    `invcode` char(10) not null,
    unique key (`zone`, `name`),
    unique key (`invcode`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerProfile`
--
drop table if exists `zhanhd.player`.`PlayerProfile`;
create table `zhanhd.player`.`PlayerProfile` (
    `pid` bigint unsigned not null,
    `k` varchar(25) not null,
    `v` text not null,

    primary key (`pid`, `k`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerCoherence`
--
drop table if exists `zhanhd.player`.`PlayerCoherence`;
create table `zhanhd.player`.`PlayerCoherence` (
    `pid` bigint unsigned not null,
    `k` varchar(25) not null,
    `v` int unsigned not null,

    primary key (`pid`, `k`)
) Engine = InnoDB;


--
-- `zhanhd.player`.`PlayerCoherenceDaily`
--
drop table if exists `zhanhd.player`.`PlayerCoherenceDaily`;
create table `zhanhd.player`.`PlayerCoherenceDaily` (
    `pid` bigint unsigned not null,
    `date` int unsigned not null,
    `k` varchar(25) not null,
    `v` int unsigned not null,

    primary key (`pid`, `date`, `k`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerBuilding`
--
drop table if exists `zhanhd.player`.`PlayerBuilding`;
create table `zhanhd.player`.`PlayerBuilding` (
    `pid` bigint unsigned not null,
    `bid` int unsigned not null,

    `lvl` tinyint unsigned not null,
    `flags` tinyint unsigned not null,
    `cltts` bigint unsigned not null,
    `ugdts` bigint unsigned not null,

    primary key (`pid`, `bid`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerEntity`
--
drop table if exists `zhanhd.player`.`PlayerEntity`;
create table `zhanhd.player`.`PlayerEntity` (
    `id` bigint unsigned not null auto_increment primary key,
    `pid` bigint unsigned not null,
    `eid` int unsigned not null,
    `lvl` smallint unsigned not null,
    `exp` mediumint unsigned not null,
    `cnt` int unsigned not null,
    `flags` smallint unsigned not null,
    `gid` tinyint unsigned not null,
    key `pe` (`pid`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerEntityProperty`
--
drop table if exists `zhanhd.player`.`PlayerEntityProperty`;
create table `zhanhd.player`.`PlayerEntityProperty` (
    `peid` bigint unsigned not null,
    `k` varchar(25) not null,
    `v` text not null,

    primary key (`peid`, `k`)
) Engine = InnoDB partition by hash(peid) partitions 8;

--
-- `zhanhd.player`.`PlayerEntityRefine`
--
drop table if exists `zhanhd.player`.`PlayerEntityRefine`;
create table `zhanhd.player`.`PlayerEntityRefine` (
    `peid` bigint unsigned not null,
    `k` varchar(25) not null,
    `v` text not null,

    primary key (`peid`, `k`)
) Engine = InnoDB partition by hash(peid) partitions 8;

--
-- `zhanhd.player`.`PlayerEntitySkill`
--
drop table if exists `zhanhd.player`.`PlayerEntitySkill`;
create table `zhanhd.player`.`PlayerEntitySkill` (
    `peid` bigint unsigned not null,
    `k` int not null,
    `v` tinyint not null,

    primary key (`peid`, `k`)
) Engine = InnoDB partition by hash(peid) partitions 8;

--
-- `zhanhd.player`.`PlayerIllustration`
--
drop table if exists `zhanhd.player`.`PlayerIllustration`;
create table `zhanhd.player`.`PlayerIllustration` (
    `pid` bigint unsigned not null,
    `eid` int unsigned not null,

    `type` int unsigned not null,

    primary key (`pid`, `eid`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerLineup`
--
drop table if exists `zhanhd.player`.`PlayerLineup`;
create table `zhanhd.player`.`PlayerLineup` (
    `pid` bigint unsigned not null,
    `gid` int unsigned not null,

    `fid` int unsigned not null,
    `power` bigint unsigned not null,
    `lvlsum` int unsigned not null,
    `captain` int unsigned not null,

    primary key (`pid`, `gid`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerLineupHero`
--
drop table if exists `zhanhd.player`.`PlayerLineupHero`;
create table `zhanhd.player`.`PlayerLineupHero` (
    `pid` bigint unsigned not null,
    `gid` int unsigned not null,

    `pos` int unsigned not null,
    `peid` bigint unsigned not null,

    primary key (`pid`, `gid`, `pos`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerLineupEquip`
--
drop table if exists `zhanhd.player`.`PlayerLineupEquip`;
create table `zhanhd.player`.`PlayerLineupEquip` (
    `pid` bigint unsigned not null,
    `gid` int unsigned not null,
    `pos` int unsigned not null,

    `k` int unsigned not null,
    `v` bigint unsigned not null,

    primary key (`pid`, `gid`, `pos`, `k`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerTask`
--
drop table if exists `zhanhd.player`.`PlayerTask`;
create table `zhanhd.player`.`PlayerTask` (
    `pid` bigint unsigned not null,
    `fid` int unsigned not null,
    `difficulty` int unsigned not null,

    `flags` tinyint unsigned not null,

    `created` bigint unsigned not null,
    `updated` bigint unsigned not null,

    primary key (`pid`, `fid`, `difficulty`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerCrusade`
--
drop table if exists `zhanhd.player`.`PlayerCrusade`;
create table `zhanhd.player`.`PlayerCrusade` (
    `pid` bigint unsigned not null,
    `cid` int unsigned not null,

    `gid`   tinyint unsigned not null,
    `flags` tinyint unsigned not null,
    `times` int     unsigned not null,

    `created` bigint unsigned not null,
    `updated` bigint unsigned not null,

    primary key (`pid`, `cid`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerReward`
--
drop table if exists `zhanhd.player`.`PlayerReward`;
create table `zhanhd.player`.`PlayerReward` (
    `id` int unsigned not null auto_increment primary key,
    `pid` bigint unsigned not null,

    `flags`    tinyint unsigned not null,
    `created`  bigint unsigned not null,
    `sendtime` bigint unsigned not null,
    `expire`   bigint unsigned not null,

    `from`   tinyint unsigned not null,
    `intval` int     unsigned not null,
    `strval` text             not null,

    key (`pid`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerRewardProfile`
--
drop table if exists `zhanhd.player`.`PlayerRewardProfile`;
create table `zhanhd.player`.`PlayerRewardProfile` (
    `prid` int unsigned not null,
    `k` varchar(128) not null,
    `v` text not null,
    primary key (`prid`, `k`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerRewardSource`
--
drop table if exists `zhanhd.player`.`PlayerRewardSource`;
create table `zhanhd.player`.`PlayerRewardSource` (
    `prid` int unsigned not null,
    `k` int unsigned not null,
    `v` int unsigned not null,
    primary key (`prid`, `k`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerRewardAccepted`
--
drop table if exists `zhanhd.player`.`PlayerRewardAccepted`;
create table `zhanhd.player`.`PlayerRewardAccepted` (
    `pid` bigint unsigned not null,
    `prid` bigint unsigned not null,
    primary key (`pid`, `prid`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerCounter`
--
drop table if exists `zhanhd.player`.`PlayerCounter`;
create table `zhanhd.player`.`PlayerCounter` (
    `pid` bigint unsigned not null,
    `k` varchar(50) not null,
    `v` int unsigned not null,

    primary key (`pid`, `k`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerCounterCycle`
--
drop table if exists `zhanhd.player`.`PlayerCounterCycle`;
create table `zhanhd.player`.`PlayerCounterCycle` (
    `pid`   bigint unsigned not null,
    `cycle` int unsigned not null,

    `k` varchar(50) not null,
    `v` int unsigned not null,

    primary key (`pid`, `cycle`, `k`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerCounterWeekly`
--
drop table if exists `zhanhd.player`.`PlayerCounterWeekly`;
create table `zhanhd.player`.`PlayerCounterWeekly` (
    `pid`   bigint unsigned not null,
    `week` int unsigned not null,

    `k` varchar(50) not null,
    `v` int unsigned not null,

    primary key (`pid`, `week`, `k`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerCounterMonthly`
--
drop table if exists `zhanhd.player`.`PlayerCounterMonthly`;
create table `zhanhd.player`.`PlayerCounterMonthly` (
    `pid`   bigint unsigned not null,
    `month` int unsigned not null,
    `k` varchar(50) not null,
    `v` int unsigned not null,
    primary key (`pid`, `month`, `k`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerRecent`
--
drop table if exists `zhanhd.player`.`PlayerRecent`;
create table `zhanhd.player`.`PlayerRecent` (
    `pid` bigint unsigned not null,
    `k`   varchar(50) not null,
    `v`   bigint unsigned not null,

    primary key (`pid`, `k`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerAchievement`
--
drop table if exists `zhanhd.player`.`PlayerAchievement`;
create table `zhanhd.player`.`PlayerAchievement` (
    `pid` bigint unsigned not null,
    `aid` int unsigned not null,

    `flags`  tinyint unsigned not null,

    `created` bigint unsigned not null,
    `updated` bigint unsigned not null,

    primary key (`pid`, `aid`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerLogger`
--
drop table if exists `zhanhd.player`.`PlayerLogger`;
create table `zhanhd.player`.`PlayerLogger` (
    `id` bigint unsigned not null auto_increment primary key,

    `pid` bigint unsigned not null,
    `cmd` int unsigned not null,

    `eid` int unsigned not null,
    `cnt` int unsigned not null,

    `peid`    int    unsigned not null,
    `created` bigint unsigned not null,

    key `pc` (`pid`, `cmd`),
    key `px` (`pid`, `eid`),
    key `pe` (`pid`, `peid`)
) Engine = InnoDB;

--
-- `zhanhd.player`,`PlayerLoggerSimple`
--
drop table if exists `zhanhd.player`.`PlayerLoggerSimple`;
create table `zhanhd.player`.`PlayerLoggerSimple` (
    `id` bigint unsigned not null auto_increment,
    `pid` bigint unsigned not null,
    `log` text not null,
    `created` bigint unsigned not null,
    primary key (`id`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerRelation`
--
drop table if exists `zhanhd.player`.`PlayerRelation`;
create table `zhanhd.player`.`PlayerRelation` (
    `id`        bigint unsigned not null auto_increment,
    `pid`       bigint unsigned not null,
    `fid`       bigint unsigned not null,
    `flags`      tinyint unsigned not null,
    `likeTimes` smallint unsigned not null,
    `lastLiked` bigint unsigned not null,
    `loveValue` tinyint unsigned not null,
    `created`   bigint unsigned not null,
    `updated`   bigint unsigned not null,
    primary key (`id`),
    unique key (`pid`, `fid`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PlayerRelationLove`
--
drop table if exists `zhanhd.player`.`PlayerRelationLove`;
create table `zhanhd.player`.`PlayerRelationLove` (
    `prid` bigint unsigned not null,
    `gear` tinyint unsigned not null,
    `eid`  int unsigned not null,
    `flag` tinyint unsigned not null,
    primary key (`prid`, `gear`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerInvite`
--
drop table if exists `zhanhd.player`.`PlayerInvite`;
create table `zhanhd.player`.`PlayerInvite` (
    `pid` bigint unsigned not null,
    `invitee` bigint unsigned not null,
    `created` bigint unsigned not null,
    primary key (`pid`, `invitee`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerDailyQuestion`
--
drop table if exists `zhanhd.player`.`PlayerDailyQuestion`;
create table `zhanhd.player`.`PlayerDailyQuestion` (
    `pid` bigint unsigned not null,
    `day` int unsigned not null,
    `qid` smallint unsigned not null,
    `answer` tinyint unsigned not null,
    `idx` tinyint unsigned not null,

    primary key (`pid`, `day`, `qid`),
    index (`idx`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`Order`
--
drop table if exists `zhanhd.player`.`Order`;
create table `zhanhd.player`.`Order` (
    `id` bigint unsigned not null auto_increment,
    `serial` varchar(32) not null,
    `pid` bigint unsigned not null,
    `merchandise` smallint unsigned not null,
    `created` bigint unsigned not null,
    `updated` bigint unsigned not null,
    `status` tinyint unsigned not null,
    primary key (`id`),
    unique key (`serial`),
    key (`pid`),
    key (`status`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`OrderWaiting`
--
drop table if exists `zhanhd.player`.`OrderWaiting`;
create table `zhanhd.player`.`OrderWaiting` (
    `id` bigint unsigned not null auto_increment,
    `uniqrec` char(32) not null unique key,
    `pid` bigint unsigned not null,
    `receipt` text not null,
    `flag` tinyint not null,
    index (`pid`),
    index (`flag`),
    primary key (`id`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`Message`
--
drop table if exists `zhanhd.player`.`Message`;
create table `zhanhd.player`.`Message` (
    `id` bigint unsigned not null auto_increment,
    `pid` bigint unsigned not null,
    `gid` bigint unsigned not null,
    `tag` tinyint unsigned not null,
    `argvs` text not null,
    `created` bigint unsigned not null,
    primary key (`id`),
    key (`pid`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerGift`
--
drop table if exists `zhanhd.player`.`PlayerGift`;
create table `zhanhd.player`.`PlayerGift` (
    `pid` bigint unsigned not null,
    `gid` varchar(24) not null,
    primary key (`pid`, `gid`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`CounterDaily`
--
drop table if exists `zhanhd.player`.`CounterDaily`;
create table `zhanhd.player`.`CounterDaily` (
    `day` int unsigned not null,
    `k` varchar(24) not null,
    `v` bigint unsigned not null,
    primary key (`day`, `k`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`Zone`
--
drop table if exists `zhanhd.player`.`Zone`;
create table `zhanhd.player`.`Zone` (
    `k` varchar(24) not null,
    `v` varchar(32) not null,
    primary key (`k`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`ZoneWhiteList`
--
drop table if exists `zhanhd.player`.`ZoneWhiteList`;
create table `zhanhd.player`.`ZoneWhiteList` (
    `id` int unsigned not null auto_increment,
    `ip` varchar(46) not null,
    primary key (`id`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`ZoneDaily`
--
drop table if exists `zhanhd.player`.`ZoneDaily`;
create table `zhanhd.player`.`ZoneDaily` (
    `date` int unsigned not null,
    `k` varchar(24) not null,
    `v` varchar(32) not null,
    primary key (`date`, `k`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerNewzoneMission`
--
drop table if exists `zhanhd.player`.`PlayerNewzoneMission`;
create table `zhanhd.player`.`PlayerNewzoneMission` (
    `pid` bigint unsigned not null,
    `mid` int unsigned not null,

    `flag` tinyint unsigned not null,

    `created` bigint unsigned not null,
    `updated` bigint unsigned not null,
    primary key (`pid`, `mid`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PlayerWeekMission`
--
drop table if exists `zhanhd.player`.`PlayerWeekMission`;
create table `zhanhd.player`.`PlayerWeekMission` (
    `pid` bigint unsigned not null,
    `week` int unsigned not null,
    `mid` int unsigned not null,

    `flag` tinyint unsigned not null,
    `created` bigint unsigned not null,
    `updated` bigint unsigned not null,
    primary key (`pid`, `week`, `mid`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`Guild`
--
drop table if exists `zhanhd.player`.`Guild`;
create table `zhanhd.player`.`Guild` (
    `id`       bigint unsigned not null auto_increment,
    `name`     varchar(32) not null,
    `lvl`      int unsigned not null,
    `exp`      int unsigned not null,
    `memnum`   int unsigned not null,
    `pending`  int unsigned not null,
    `bulletin` varchar(128) not null,
    `founder`  bigint unsigned not null,
    `created`  bigint unsigned not null,
    `lastjoin` bigint unsigned not null,
    unique key (`name`),
    primary key (`id`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`GuildMember`
--
drop table if exists `zhanhd.player`.`GuildMember`;
create table `zhanhd.player`.`GuildMember` (
    `gid` bigint unsigned not null,
    `pid` bigint unsigned not null,
    `post` tinyint unsigned not null,
    `cont` int unsigned not null,
    `contused` int unsigned not null,
    `join` bigint unsigned not null,
    primary key (`gid`, `pid`),
    index(`post`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`GuildMemberDaily`
--
drop table if exists `zhanhd.player`.`GuildMemberDaily`;
create table `zhanhd.player`.`GuildMemberDaily` (
    `gid` bigint unsigned not null,
    `pid` bigint unsigned not null,
    `date` int unsigned not null,
    `k` varchar(50) not null,
    `v` int unsigned not null,
    primary key (`gid`, `pid`, `date`, `k`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`GuildPending`
--
drop table if exists `zhanhd.player`.`GuildPending`;
create table `zhanhd.player`.`GuildPending` (
    `gid` bigint unsigned not null,
    `pid` bigint unsigned not null,
    `time` bigint unsigned not null,
    primary key (`gid`, `pid`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`GuildImpeach`
--
drop table if exists `zhanhd.player`.`GuildImpeach`;
create table `zhanhd.player`.`GuildImpeach` (
    `gid` bigint unsigned not null,
    `time` bigint unsigned not null,
    primary key (`gid`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`GuildImpeachMember`
--
drop table if exists `zhanhd.player`.`GuildImpeachMember`;
create table `zhanhd.player`.`GuildImpeachMember` (
    `gid` bigint unsigned not null,
    `pid` bigint unsigned not null,
    primary key (`gid`, `pid`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`ActivityPlan`
--
drop table if exists `zhanhd.player`.`ActivityPlan`;
create table `zhanhd.player`.`ActivityPlan` (
    `id`     int unsigned not null auto_increment,
    `preview` int unsigned not null,
    `begin`  int unsigned not null,
    `end`    int unsigned not null,
    `type`   tinyint unsigned not null,

    primary key (`id`),
    index (`type`)
) Engine = InnoDB;


--
-- `zhanhd.player`.`ActivityHistory`
--
drop table if exists `zhanhd.player`.`ActivityHistory`;
create table `zhanhd.player`.`ActivityHistory` (
    `aid`   int unsigned not null,
    `pid`   bigint unsigned not null,
    `rank`  int unsigned not null,
    `score` int unsigned not null,

    primary key (`aid`, `pid`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`ActivityHistoryProfile`
--
drop table if exists `zhanhd.player`.`ActivityHistoryProfile`;
create table `zhanhd.player`.`ActivityHistoryProfile` (
    `aid` int unsigned not null,
    `pid` bigint unsigned not null,
    `k`   varchar(24) not null,
    `v`   varchar(32) not null,

    primary key (`aid`, `pid`, `k`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`RobTarlist`
--
drop table if exists `zhanhd.player`.`RobTarlist`;
create table `zhanhd.player`.`RobTarlist` (
    `pid` bigint unsigned not null,
    `robbed` tinyint unsigned not null,
    `total` tinyint unsigned not null,
    `refresh` bigint unsigned not null,
    primary key (`pid`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`RobTarget`
--
drop table if exists `zhanhd.player`.`RobTarget`;
create table `zhanhd.player`.`RobTarget` (
    `pid` bigint unsigned not null,
    `tid` bigint unsigned not null,
    `status` tinyint unsigned not null,
    primary key (`pid`, `tid`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`RobLog`
--
drop table if exists `zhanhd.player`.`RobLog`;
create table `zhanhd.player`.`RobLog` (
    `id`      bigint unsigned auto_increment not null,
    `pid`     bigint unsigned not null,
    `robber`  bigint unsigned not null,
    `retval`  bigint unsigned not null,
    `replay`  bigint unsigned not null,
    `weapon`  bigint unsigned not null,
    `armor`   bigint unsigned not null,
    `soldier` bigint unsigned not null,
    `gold`    bigint unsigned not null,
    `horse`   bigint unsigned not null,
    `revenged` bigint unsigned not null,
    `created` bigint unsigned not null,
    primary key (`id`),
    index(`pid`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`Replay`
--
drop table if exists `zhanhd.player`.`Replay`;
create table `zhanhd.player`.`Replay` (
    `id` bigint unsigned auto_increment not null,
    `attacker` bigint unsigned not null,
    `defender` bigint unsigned not null,
    `combat` blob not null,
    `access` tinyint unsigned not null,
    `release` bigint unsigned not null,
    `created` bigint unsigned not null,
    primary key (`id`)
) Engine = InnoDB;

##################################################

--
-- `zhanhd.player`.`PointsRace`
--
drop table if exists `zhanhd.player`.`PointsRace`;
create table `zhanhd.player`.`PointsRace` (
    `cycle` int unsigned not null,
    `pid` bigint unsigned not null,
    `buff` tinyint unsigned not null,
    `cwin` smallint unsigned not null,
    `challenged` smallint unsigned not null,
    `listWin` tinyint unsigned not null,
    `listTotal` tinyint unsigned not null,
    primary key (`cycle`, `pid`)
) Engine = InnoDB;

--
-- `zhanhd.player`.`PointsRaceDaily`
--
drop table if exists `zhanhd.player`.`PointsRaceDaily`;
create table `zhanhd.player`.`PointsRaceDaily` (
    `cycle` int unsigned not null,
    `cday`  tinyint unsigned not null,
    `pid` bigint unsigned not null,
    `challenged` tinyint unsigned not null,
    `refreshed` tinyint unsigned not null,
    `conswin` tinyint unsigned not null,
    primary key (`cycle`, `cday`, `pid`)
) Engine = InnoDB;

--
--  `zhanhd.player`.`PointsRaceTarget`
--
drop table if exists `zhanhd.player`.`PointsRaceTarget`;
create table `zhanhd.player`.`PointsRaceTarget` (
    `cycle` int unsigned not null,
    `pid` bigint unsigned not null,
    `tid` bigint unsigned not null,
    `status` tinyint unsigned not null,
    primary key (`cycle`, `pid`, `tid`)
) Engine = InnoDB;