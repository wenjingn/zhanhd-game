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