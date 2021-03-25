<?php
/**
 * $Id$
 */

/**
 *
 */
require '/data/php/games/zhanhd/shell/parser/ExcelParser.php';

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Config\Entity;

/**
 *
 */
$argvs = (new Object)->import(getlongopt([
    'excel' => false,
    'calls' => false,
]));

/**
 *
 */
class ZhanhdConfigParser extends ExcelParser
{
    /* prop handler */
    public function prop_handler($excel, $sheet = 'Goods')
    {
        /* prop type contains
         * chest, prop, gift */
printf("DELETE ep.* FROM `zhanhd.config`.`EntityProperty` ep LEFT JOIN `zhanhd.config`.`Entity` e ON (ep.eid=e.id) WHERE e.type in (%d, %d, %d, %d);\n",
Entity::TYPE_CHEST, Entity::TYPE_PROP, Entity::TYPE_GIFT, Entity::TYPE_FRAGMENT);
printf("DELETE FROM `zhanhd.config`.`Entity` WHERE `type` in (%d, %d, %d, %d);\n",
Entity::TYPE_CHEST, Entity::TYPE_PROP, Entity::TYPE_GIFT, Entity::TYPE_FRAGMENT);

        $TYPE_TAB = [
            1 => Entity::TYPE_PROP,
            2 => Entity::TYPE_CHEST,
            4 => Entity::TYPE_GIFT,
            5 => Entity::TYPE_FRAGMENT,
        ];

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $type = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            if ($type == 3) {
                continue;
            }

            $type = $TYPE_TAB[$type];

            $name = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();

            printf("INSERT INTO `zhanhd.config`.`Entity` VALUES (%d, '%s', %d, 0, 0, 0, 0, 0, 1);\n", $id, $name, $type);
            if ($type == Entity::TYPE_PROP) {
                $service = $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue();
                if ($service) {
                    printf("INSERT INTO `zhanhd.config`.`EntityProperty` VALUES (%d, 'service', '%s');\n", $id, $service);
                }
            } else if ($type == Entity::TYPE_CHEST) {
                for ($col = 5; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                    $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                    printf("INSERT INTO `zhanhd.config`.`EntityProperty` VALUES (%d, %d, %d);\n", $id, $eid, $num);
                }
            } else if ($type == Entity::TYPE_GIFT) {
                $loveValue = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`EntityProperty` VALUES (%d, 'love', %d);\n", $id, $loveValue);
            } else if ($type == Entity::TYPE_FRAGMENT) {
                $cohesion = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
                $require  = $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`EntityProperty` VALUES (%d, %d, %d);\n", $id, $cohesion, $require);
            }

        }
    }

    /* prop-store handler */
    public function propstore_handler($excel, $sheet = 'prop')
    {
        printf("TRUNCATE `zhanhd.config`.`PropGoods`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue()); $row++) {
            $classif = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            if ($classif == 1 || $classif == 0) {
                continue;
            }
            $eid = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue();
            if ($id == 203 || $id == 215) {
                $eid = 0;
            }
            $price = $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue();
            $incr = $sheet->GetCellByColumnAndRow(10, $row)->getCalculatedValue();
            if ($incr) {
                $incr = 10;
            }

            printf("INSERT INTO `zhanhd.config`.`PropGoods` VALUES (%d, %d, %d, %d);\n", $id, $price, $incr, $eid);
        }
    }

    /* soul handler */
    public function soul_handler($excel)
    {
        printf("DELETE FROM `zhanhd.config`.`Entity` WHERE `type`=%d;\n", Entity::TYPE_SOUL);

        $sheet = $excel->getSheetByName('soul');
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $tag = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`Entity` VALUES (%d, '%s', 15, 0, 0, 0, 0, 0, 1);\n", $id, $tag);
        }
    }

    /* heroexp handler */
    public function heroexp_handler($excel, $sheet = 'heroexp')
    {
        printf("TRUNCATE `zhanhd.config`.`EntityExperience`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 3; null != ($lvl = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $exp = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`EntityExperience` VALUES (%d, %d, %d);\n", $lvl, Entity::TYPE_HERO, $exp);
        }
    }

    /* enhance handler */
    public function enhance_handler($excel, $sheet = 'enhance')
    {
        printf("TRUNCATE `zhanhd.config`.`EntityEnhance`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null !== ($lvl = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $exp = $sheet->getCellByColumnAndRow(1, $row + 1)->getCalculatedValue();
            $str = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $int = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            $stm = $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue();
            $dex = $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue();
            $dmg = $sheet->getCellByColumnAndRow(6, $row)->getCalculatedValue();
            $hpt = $sheet->getCellByColumnAndRow(7, $row)->getCalculatedValue();
            $def = $sheet->getCellByColumnAndRow(8, $row)->getCalculatedValue();
            $slvl = $sheet->getCellByColumnAndRow(9, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`EntityEnhance` VALUES (%d, %d, %d, %d, %d, %d, %d, %d, %d, %d);\n",
                $lvl, $exp, $str, $int, $stm, $dex, $dmg, $hpt, $def, $slvl
            );
        }
    }

    /* signin handler*/
    public function signin_handler($excel, $sheet = 'sign')
    {
        printf("TRUNCATE `zhanhd.config`.`SigninReward`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($dom = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $eid = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $num = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $vipnum = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`SigninReward` VALUES (%d, %d, %d, %d);\n",
                $dom,
                $eid,
                $num,
                $vipnum
            );
        }
    }

    /* deposit handler */
    public function depositReward_handler($excel, $sheet = 'payreback')
    {
        printf("TRUNCATE `zhanhd.config`.`DepositReward`;\n");
        printf("TRUNCATE `zhanhd.config`.`DepositRewardSource`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $limit = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`DepositReward` VALUES (%d, %d);\n", $id, $limit);
            for ($col = 2; $eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue(); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`DepositRewardSource` VALUES (%d, %d, %d);\n", $id, $eid, $num);
            }
        }
    }

    /* invite handler */
    public function inviteReward_handler($excel, $sheet = 'invite')
    {
        printf("TRUNCATE `zhanhd.config`.`InviteReward`;\n");
        printf("TRUNCATE `zhanhd.config`.`InviteRewardSource`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $limit = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`InviteReward` VALUES (%d, %d);\n", $id, $limit);
            for ($col = 2; $eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue(); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`InviteRewardSource` VALUES (%d, %d, %d);\n", $id, $eid, $num);
            }
        }
    }

    /* greener reward handler */
    public function greenerReward_handler($excel, $sheet = 'day7')
    {
        printf("truncate `zhanhd.config`.`GreenerReward`;\n");
        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($day = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $eid = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $num = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`GreenerReward` VALUES (%d, %d, %d);\n", $day, $eid, $num);
        }
    }

    /* question handler */
    public function question_handler($excel, $sheet = 'qa')
    {
        printf("TRUNCATE `zhanhd.config`.`Question`;\n");
        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $answer = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`Question` VALUES (%d, %d);\n", $id, $answer);
        }
    }

    /* question reward handler */
    public function questionReward_handler($excel, $sheet = 'qareward')
    {
        printf("TRUNCATE `zhanhd.config`.`QuestionReward`;\n");
        printf("TRUNCATE `zhanhd.config`.`QuestionRewardSource`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $score = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`QuestionReward` VALUES (%d, %d);\n", $id, $score);

            for ($col = 2; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`QuestionRewardSource` VALUES (%d, %d, %d);\n", $id, $eid, $num);
            }
        }
    }

    /* actins handler */
    public function actins_handler($excel, $sheet = 'actins', $sheet2 = 'actower', $sheet3 = 'ActivityReward')
    {
        printf("TRUNCATE `zhanhd.config`.`ActIns`;\n");
        printf("TRUNCATE `zhanhd.config`.`ActInsFloor`;\n");
        printf("TRUNCATE `zhanhd.config`.`ActInsFloorNpc`;\n");
        printf("TRUNCATE `zhanhd.config`.`ActInsFloorDrop`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $name   = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $npcnum = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            $armytp = $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue();
            $rarity = $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`ActIns` VALUES (%d, '%s', %d, %d, %d);\n", $id, $name, $npcnum, $armytp, $rarity);
        }

        $sheet = $excel->getSheetByName($sheet2);
        $currFloor = 0;
        for ($row = 2, $pos = 0; null != ($aid = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++, $pos++) {
            $floor = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $floorId = $aid * 100 + $floor;
            if ($currFloor != $floorId) {
                $fmt = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`ActInsFloor` VALUES (%d, %d, %d);\n", $aid, $floor, $fmt);
                $currFloor = $floorId;
                $pos = 0;
            }

            $eid = $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue();
            $lvl = $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue();
            $ehc = $sheet->getCellByColumnAndRow(6, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`ActInsFloorNpc` VALUES (%d, %d, %d, %d, %d, %d);\n", $aid, $floor, $pos, $eid, $lvl, $ehc);
        }

        $sheet = $excel->getSheetByName($sheet3);
        for ($row = 2; null != ($aid = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $floor = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            for ($col = 2; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`ActInsFloorDrop` VALUES (%d, %d, %d, %d);\n", $aid, $floor, $eid, $num);
            }
        }
    }

    /* reward handler */
    public function reward_handler($excel, $sheet = '竞技场排行奖励', $sheet2 = 'ActivityChart', $sheet3 = '世界BOSS奖励', $sheet4 = 'BOSS伤害奖励', $sheet5 = '争霸赛奖励')
    {
        printf("TRUNCATE `zhanhd.config`.`Reward`;\n");
        printf("TRUNCATE `zhanhd.config`.`RewardRank`;\n");
        printf("TRUNCATE `zhanhd.config`.`RewardRankSource`;\n");
        printf("TRUNCATE `zhanhd.config`.`RewardRankCoherence`;\n");
        printf("TRUNCATE `zhanhd.config`.`RewardScore`;\n");
        printf("TRUNCATE `zhanhd.config`.`RewardScoreSource`;\n");

        /* pvprank reward */
        $pvprankRewardId = 1;
        printf("INSERT INTO `zhanhd.config`.`Reward` VALUES (%d, '竞技场');\n", $pvprankRewardId);
        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($lowRank = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $id = $row - 1;
            printf("INSERT INTO `zhanhd.config`.`RewardRank` VALUES (%d, %d, %d);\n", $pvprankRewardId, $id, $lowRank);
            for ($col = 1; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`RewardRankSource` VALUES (%d, %d, %d, %d);\n", $pvprankRewardId, $id, $eid, $num);
            }
        }

        /* actins reward */
        $actinsRewardId = 2;
        printf("INSERT INTO `zhanhd.config`.`Reward` VALUES (%d, '活动副本');\n", $actinsRewardId);
        $sheet = $excel->getSheetByName($sheet2);
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $lowRank = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`RewardRank` VALUES (%d, %d, %d);\n", $actinsRewardId, $id, $lowRank);
            for ($col = 2; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`RewardRankSource` VALUES (%d, %d, %d, %d);\n", $actinsRewardId, $id, $eid, $num);
            }
        }

        /* worldboss reward */
        $rewardId = 3;
        printf("INSERT INTO `zhanhd.config`.`Reward` VALUES (%d, '世界BOSS');\n", $rewardId);
        $sheet = $excel->getSheetByName($sheet3);
        for ($row = 2; null != ($lowRank = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $id = $row-1;
            printf("INSERT INTO `zhanhd.config`.`RewardRank` VALUES (%d, %d, %d);\n", $rewardId, $id, $lowRank);

            $medal = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`RewardRankCoherence` VALUES (%d, %d, 'medal', %d);\n", $rewardId, $id, $medal);
            
            for ($col = 2; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`RewardRankSource` VALUES (%d, %d, %d, %d);\n", $rewardId, $id, $eid, $num);
            }
        }

        $sheet = $excel->getSheetByName($sheet4);
        for ($row = 2; null != ($damage = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $id = $row-1;
            printf("INSERT INTO `zhanhd.config`.`RewardScore` VALUES (%d, %d, %d);\n", $rewardId, $id, $damage);
            for ($col = 1; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`RewardScoreSource` VALUES (%d, %d, %d, %d);\n", $rewardId, $id, $eid, $num);
            }
        }
        
        /* top32 reward */
        $rewardId = 4;
        printf("INSERT INTO `zhanhd.config`.`Reward` VALUES (%d, '32强争霸赛');\n", $rewardId);
        $sheet = $excel->getSheetByName($sheet5);
        for ($row = 2; null != ($lowRank = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $id = $row - 1;
            printf("INSERT INTO `zhanhd.config`.`RewardRank` VALUES (%d, %d, %d);\n", $rewardId, $id, $lowRank);
            $num = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`RewardRankCoherence` VALUES (%d, %d, '%s', %d);\n", $rewardId, $id, 'medal', $num);
            for ($col = 2; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`RewardRankSource` VALUES (%d, %d, %d, %d);\n", $rewardId, $id, $eid, $num);
            }
        }
    }

    /* gift handler */
    public function gift_handler($excel, $sheet = 'gift')
    {
        printf("TRUNCATE `zhanhd.config`.`Gift`;\n");
        printf("TRUNCATE `zhanhd.config`.`GiftSource`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $type = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            if ($type == 2) {
                continue;
            }
            $release = strtotime($sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue());
            $expire = strtotime($sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue());
            printf("INSERT INTO `zhanhd.config`.`Gift` VALUES ('%s', %d, %d, %d);\n", $id, $type, $release, $expire);

            for ($col = 5; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col += 2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`GiftSource` VALUES ('%s', %d, %d);\n", $id, $eid, $num);
            }
        }
    }

    /* leader handler */
    public function leader_handler($excel, $sheet = 'leaderimage')
    {
        printf("TRUNCATE `zhanhd.config`.`Leader`;\n");

        $TAB = [
            1 => 'hair',
            2 => 'face',
            3 => 'clothes',
            11 => 'hairclr',
            33 => 'eyeclr',
        ];
        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null !== ($sex = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $part = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $part = $TAB[$part];
            $val = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $vip = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`Leader` VALUES (%d, '%s', %d, %d);\n", $sex, $part, $val, $vip);
        }
    }

    /* resourceRecruit handler */
    public function resourceRecruit_handler($excel)
    {
        printf("DELETE FROM `zhanhd.config`.`ResourceRecruit`;\n");
        printf("DELETE FROM `zhanhd.config`.`ResourceRecruitGroup`;\n");
        printf("DELETE FROM `zhanhd.config`.`ResourceRecruitPercentage`;\n");

        $sheet = $excel->getSheetByName('ResourceDraw1');
        for ($row = 2; null !== $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue(); $row++) {
            $total = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $prop  = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $prob1 = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            $prob2 = $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue();
            $prob3 = $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue();
            $prob4 = $sheet->getCellByColumnAndRow(6, $row)->getCalculatedValue();
            $prob5 = $sheet->getCellByColumnAndRow(7, $row)->getCalculatedValue();

            $tag = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`ResourceRecruit` VALUES (null, %d, %d, %d, %d, %d, %d, %d);\n",
                $total, $prop, $prob1, $prob2, $prob3, $prob4, $prob5
            );
        }

        $sheet = $excel->getSheetByName('ResourceDraw2');
        for ($row = 2; null !== ($weapon = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $a200    = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $armor   = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $a300    = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            $soldier = $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue();
            $a400    = $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue();
            $horse   = $sheet->getCellByColumnAndRow(6, $row)->getCalculatedValue();
            $a100    = $sheet->getCellByColumnAndRow(7, $row)->getCalculatedValue();

            printf("INSERT INTO `zhanhd.config`.`ResourceRecruitPercentage` VALUES (null, %d, %d, %d, %d, %d, %d, %d, %d);\n",
                $weapon, $a200, $armor, $a300, $soldier, $a400, $horse, $a100
            );
        }

        $sheet = $excel->getSheetByName('武将分组');
        for ($row = 2; null !== ($gid = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            for ($col = 2; null !== ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col++) {
                printf("INSERT INTO `zhanhd.config`.`ResourceRecruitGroup` VALUES (%d, %d, 1);\n", $gid, $eid);
            }
        }
    }

    /* fixedTimeReward handler */
    public function fixedTimeReward_handler($excel)
    {
        printf("DELETE FROM `zhanhd.config`.`FixedTimeReward`;\n");
        printf("DELETE FROM `zhanhd.config`.`FixedTimeRewardSource`;\n");

        $sheet = $excel->getSheetByName('在线奖励');
        for ($row = 2; null !== ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            printf("INSERT INTO `zhanhd.config`.`FixedTimeReward` VALUES (%d, %d);\n",
                $id,
                $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue()
            );

            for ($col = 2; null !== ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col++) {
                if (null !== ($num = $sheet->getCellByColumnAndRow(++$col, $row)->getCalculatedValue())) {
                    printf("INSERT INTO `zhanhd.config`.`FixedTimeRewardSource` VALUES (%d, %d, %d);\n",
                        $id, $eid, $num
                    );
                }
            }
        }
    }

    /* rechargeReward handler */
    public function rechargeReward_handler($excel)
    {
        printf("DELETE FROM `zhanhd.config`.`RechargeReward`;\n");
        printf("DELETE FROM `zhanhd.config`.`RechargeRewardSource`;\n");

        $sheet = $excel->getSheetByName('单充奖励');
        for ($row = 2; null !== ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            printf("INSERT INTO `zhanhd.config`.`RechargeReward` VALUES (%d, %d);\n",
                $id,
                $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue()
            );

            for ($col = 3; null !== ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col++) {
                if (null !== ($num = $sheet->getCellByColumnAndRow(++$col, $row)->getCalculatedValue())) {
                    printf("INSERT INTO `zhanhd.config`.`RechargeRewardSource` VALUES (%d, %d, %d);\n",
                        $id, $eid, $num
                    );
                }
            }
        }
    }

    /* new zone mission handler */
    public function newzoneMission_handler($excel, $sheet = 'mission7')
    {
        printf("TRUNCATE `zhanhd.config`.`NewzoneMission`;\n");
        printf("TRUNCATE `zhanhd.config`.`NewzoneMissionReward`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($day = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $idx = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $type = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $intval = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            $extra = $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue();
            $id = $day * 100 + $idx;
            printf("INSERT INTO `zhanhd.config`.`NewzoneMission` VALUES (%d, %d, %d, %d);\n", $id, $type, $intval, $extra);
            for ($col = 5; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col += 2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`NewzoneMissionReward` VALUES (%d, %d, %d);\n", $id, $eid, $num);
            }
        }
    }

    /* week mission type handler */
    public function weekMissionType_handler($excel, $sheet = '周任务-类型')
    {
        printf("TRUNCATE `zhanhd.config`.`WeekMissionType`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($type = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $flag = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $pos  = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`WeekMissionType` VALUES ($type, $flag, $pos);\n");
        }
    }

    /* week mission handler */
    public function weekMission_handler($excel, $sheet = '周任务')
    {
        printf("TRUNCATE `zhanhd.config`.`WeekMission`;\n");
        printf("TRUNCATE `zhanhd.config`.`WeekMissionReward`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 3; null != ($id = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue()); $row++) {
            $type = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $intval = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`WeekMission` VALUES (%d, %d, %d);\n", $id, $type, $intval);
            for ($col = 4; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()) && $col <= 8; $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`WeekMissionReward` VALUES (%d, %d, %d);\n", $id, $eid, $num);
            }
        }
    }

    /* dayins handler */
    public function dayins_handler($excel, $sheet = '日常副本', $sheet2 = '日常副本-阵容')
    {
        printf("TRUNCATE `zhanhd.config`.`DayIns`;\n");
        printf("TRUNCATE `zhanhd.config`.`DayInsDiff`;\n");
        printf("TRUNCATE `zhanhd.config`.`DayInsDrop`;\n");
        printf("TRUNCATE `zhanhd.config`.`DayInsNPC`;\n");

        $sheet = $excel->getSheetByName($sheet);
        $lastId = -1;
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            if ($id != $lastId) {
                $unlock = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`DayIns` VALUES (%d, %d);\n", $id, $unlock);
                $lastId = $id;
            }

            $diff = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $rlvl = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            $fmt  = $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`DayInsDiff` VALUES (%d, %d, %d, %d);\n", $id, $diff, $rlvl, $fmt);
            for ($col = 6; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`DayInsDrop` VALUES (%d, %d, %d, %d);\n", $id, $diff, $eid, $num);
            }
        }

        $sheet = $excel->getSheetByName($sheet2);

        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $diff = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $pos  = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $eid  = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            $lvl  = $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue();
            $ehc  = $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`DayInsNPC` VALUES (%d, %d, %d, %d, %d, %d);\n", $id, $diff, $pos, $eid, $lvl, $ehc);
        }
    }

    /* forge handler */
    public function forge_handler($excel)
    {
        printf("DELETE FROM `zhanhd.config`.`Forge`;\n");
        printf("DELETE FROM `zhanhd.config`.`EntityProperty` WHERE `k` IN ('forgeMaxLvl', 'forgeType', 'forgeAdd', 'forgeProp');\n");

        $sheet = $excel->getSheetByName('entity');
        for ($row = 2; null !== ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            if (null === ($lvl = $sheet->getCellByColumnAndRow(20, $row)->getCalculatedValue())) {
                continue;
            }

            printf("INSERT INTO `zhanhd.config`.`EntityProperty` VALUES (%d, 'forgeMaxLvl', %d);\n",
                $id, $lvl
            );
            printf("INSERT INTO `zhanhd.config`.`EntityProperty` VALUES (%d, 'forgeType', %d);\n",
                $id, $sheet->getCellByColumnAndRow(21, $row)->getCalculatedValue()
            );
            printf("INSERT INTO `zhanhd.config`.`EntityProperty` VALUES (%d, 'forgeAdd', %d);\n",
                $id, $sheet->getCellByColumnAndRow(22, $row)->getCalculatedValue()
            );
            printf("INSERT INTO `zhanhd.config`.`EntityProperty` VALUES (%d, 'forgeProp', %d);\n",
                $id, $sheet->getCellByColumnAndRow(23, $row)->getCalculatedValue()
            );
        }

        $sheet = $excel->getSheetByName('锻造等级与消耗');
        for ($row = 2; null !== ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $prop = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $gold = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();

            printf("INSERT INTO `zhanhd.config`.`Forge` VALUES (%d, %d, %d);\n",
                $id, $prop, $gold
            );
        }
    }

    /* guild handler */
    public function guild_handler($excel, $sheet = 'guildgx', $sheet2 = 'guildexp', $sheet3 = 'guildgift', $sheet4 = 'guildweekbag')
    {
        printf("TRUNCATE `zhanhd.config`.`GuildContribution`;\n");
        printf("TRUNCATE `zhanhd.config`.`GuildExp`;\n");
        printf("TRUNCATE `zhanhd.config`.`GuildGift`;\n");
        printf("TRUNCATE `zhanhd.config`.`GuildGiftSource`;\n");
        printf("TRUNCATE `zhanhd.config`.`GuildChest`;\n");
        printf("TRUNCATE `zhanhd.config`.`GuildChestSource`;\n");
        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null !== ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $eid = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $num = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            $cont = $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue();
            $friendship = $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`GuildContribution` VALUES (%d, %d, %d, %d, %d);\n", $id, $eid, $num, $cont, $friendship);
        }

        $sheet = $excel->getSheetByName($sheet2);
        $expsum = 0;
        for ($row = 2; null !== ($lvl = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            printf("INSERT INTO `zhanhd.config`.`GuildExp` VALUES (%d, %d);\n", $lvl, $expsum);
            $expsum += $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
        }

        $sheet = $excel->getSheetByName($sheet3);
        for ($row = 2; null !== ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $lvl = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`GuildGift` VALUES (%d, %d);\n", $id, $lvl);
            for ($col = 2; null !== ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`GuildGiftSource` VALUES (%d, %d, %d);\n", $id, $eid, $num);
            }
        }

        $sheet = $excel->getSheetByName($sheet4);
        for ($row = 2; null !== ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $score = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`GuildChest` VALUES (%d, %d);\n", $id, $score);
            for ($col = 2; null !== ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`GuildChestSource` VALUES (%d, %d, %d);\n", $id, $eid, $num);
            }
        }
    }

    /* diares handler */
    public function diarec_handler($excel, $sheet = '限时招募')
    {
        printf("TRUNCATE `zhanhd.config`.`ActDiaRec`;\n");
        printf("TRUNCATE `zhanhd.config`.`ActDiaRecSource`;\n");
        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null !== ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $times = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`ActDiaRec` VALUES (%d, %d);\n", $id, $times);
            for ($col = 2; null !== ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`ActDiaRecSource` VALUES (%d, %d, %d);\n", $id, $eid, $num);
            }
        }
    }

	/* bond handler */
	public function bond_handler($excel, $sheet = '羁绊')
	{
		printf("TRUNCATE `zhanhd.config`.`Bond`;\n");
		printf("TRUNCATE `zhanhd.config`.`BondEffect`;\n");
		printf("TRUNCATE `zhanhd.config`.`BondMember`;\n");

		$sheet = $excel->getSheetByName($sheet);
		for ($row = 3; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
			$name = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
			printf("INSERT INTO `zhanhd.config`.`Bond` VALUES (%d, '%s');\n", $id, $name);
			for ($col = 2; $col < 6 && null !== ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col += 2) {
				$type = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
				$value = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
				printf("INSERT INTO `zhanhd.config`.`BondEffect` VALUES (%d, %d, %d);\n", $id, $type, $value);
			}

			for ($col = 6; null !== ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col++) {
				printf("INSERT INTO `zhanhd.config`.`BondMember` VALUES (%d, %d);\n", $id, $eid);
			}
		}
	}

    /* completion reward */
    public function completionReward_handler($excel, $sheet = '日常任务完成奖励')
    {
        printf("TRUNCATE `zhanhd.config`.`CompletionReward`;\n");
        printf("TRUNCATE `zhanhd.config`.`CompletionRewardSource`;\n");

        $sheet = $excel->getSheetByName($sheet);
        $type = 1;
        for ($row = 2; null != ($completion = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            printf("INSERT INTO `zhanhd.config`.`CompletionReward` VALUES (%d, %d, %d);\n", $type, $row-1, $completion);
            for ($col = 1; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col += 2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`CompletionRewardSource` VALUES (%d, %d, %d, %d);\n", $type, $row-1, $eid, $num);
            }
        }
    }
}


$parser = new ZhanhdConfigParser($argvs->excel, $argvs->calls);
$parser->exec();
