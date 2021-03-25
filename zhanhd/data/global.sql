####################################################################################################
--
-- `zhanhd.global`.`User`
--
drop table if exists `zhanhd.global`.`User`;
create table `zhanhd.global`.`User` (
    `id` bigint unsigned not null auto_increment primary key,
    `platform` tinyint not null,
    `login` varchar(32) binary not null,
    `passwd` varchar(255) not null,
    `email` varchar(30) not null,
    `flags` smallint unsigned not null,
    `created` bigint unsigned not null,
    `lastLogin` bigint unsigned not null,

    unique key (`platform`, `login`)
) Engine = InnoDB;

--
-- `zhanhd.global`.`UserProfile`
--
drop table if exists `zhanhd.global`.`UserProfile`;
create table `zhanhd.global`.`UserProfile` (
    `uid` bigint unsigned not null,
    `k` varchar(25) not null,
    `v` text not null,

    primary key (`uid`, `k`)
) Engine = InnoDB;

####################################################################################################