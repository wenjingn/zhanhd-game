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

class AttachConfigParser extends ExcelParser
{
    /* randpack handler */
    public function randpack_handler($excel, $sheet = '随机包', $sheet2 = '随机包-附件')
    {
        printf("DELETE FROM `zhanhd.config`.`Entity` WHERE type=14;\n");
        printf("TRUNCATE `zhanhd.config`.`EntityDrop`;\n");
        
        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $tag = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`Entity` VALUES (%d, '%s', %d, %d, %d, %d, %d, %d, %d);\n", $id, $tag, 14, 0, 0, 0, 0, 0, 1);
        }

        $sheet = $excel->getSheetByName($sheet2);
        for ($row = 2; null != ($eid = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $k = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $v = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $prob = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`EntityDrop` VALUES (%d, %d, %d, %d);\n", $eid, $k, $v, $prob);
        }
    }

    /* growpack handler */
    public function growpack_handler($excel, $sheet = '成长宝箱')
    {
        printf("DELETE ep.* FROM `zhanhd.config`.`EntityProperty` ep LEFT JOIN `zhanhd.config`.`Entity` e ON (ep.eid=e.id) WHERE e.type=%d;\n", 
        Entity::TYPE_GROWPACK);
        printf("DELETE er.* FROM `zhanhd.config`.`EntityUseRule` er LEFT JOIN `zhanhd.config`.`Entity` e ON (er.eid=e.id) WHERE e.type=%d;\n",
        Entity::TYPE_GROWPACK);
        printf("DELETE FROM `zhanhd.config`.`Entity` WHERE type=%d;\n", Entity::TYPE_GROWPACK);
        
        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $tag = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`Entity` VALUES (%d, '%s', %d, %d, %d, %d, %d, %d, %d);\n", $id, $tag, 17, 0, 0, 0, 0, 0, 1);
            $diff = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $ins  = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`EntityUseRule` VALUES (%d, %d, %d);\n", $id, $diff, $ins);
            for ($col = 4; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`EntityProperty` VALUES (%d, %d, %d);\n", $id, $eid, $num);
            }
        }
    }

    /* mopup handler */
    public function mopup_handler($excel, $sheet = '普通扫荡', $sheet2 = '精英扫荡', $sheet3 = '地狱扫荡')
    {
        printf("TRUNCATE `zhanhd.config`.`Battle`;\n");
        printf("TRUNCATE `zhanhd.config`.`BattleReward`;\n");

        $list = [
            1 => $excel->getSheetByName($sheet),
            2 => $excel->getSheetByName($sheet2),
            3 => $excel->getSheetByName($sheet3),
        ];
        foreach ($list as $diff => $sheet) {
            for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
                $power = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
                $exp   = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
                
                printf("INSERT INTO `zhanhd.config`.`Battle` VALUES (%d, %d, %d, %d);\n", $id, $diff, $exp, $power);
                for ($col = 3, $i = 0; null != ($prob = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=3, $i++) {
                    $eid = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                    $num = $sheet->getCellByColumnAndRow($col+2, $row)->getCalculatedValue();
                    printf("INSERT INTO `zhanhd.config`.`BattleReward` VALUES (%d, %d, %d, %d, %d, %d);\n", $id, $diff, $i, $prob, $eid, $num);
                }
            }
        }
    }

    /* friendshipStore handler */
    public function friendshipStore_handler($excel, $sheet = '友情商城')
    {
        printf("TRUNCATE `zhanhd.config`.`FriendShipGoods`;\n");
        
        $sheet = $excel->getSheetByName($sheet);
        for ($row = 3; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $eid = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $price = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $prob = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`FriendShipGoods` VALUES (%d, %d, %d, %d);\n", $id, $eid, $price, $prob);
        }
    }

    /* egroup handler */
    public function egroup_handler($excel, $sheet = '武将分组')
    {
        printf("TRUNCATE `zhanhd.config`.`EntityGroup`;\n");

        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($gid = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            
            for ($col = 2; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col++) {
                printf("INSERT INTO `zhanhd.config`.`EntityGroup` VALUES (%d, %d);\n", $gid, $eid);
            }
        }
    }

    /* zombie handler 
     * export php data file */
    public function zombie_handler($excel, $sheet = '竞技场僵尸')
    {
        $sheet = $excel->getSheetByName($sheet);
        $data = [];
        for ($row = 2; null != ($rank = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $lvl = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $elvl = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            $weights = [];
            $i = 1;
            for ($col = 3; $col < 8; $col++, $i++) {
                $weights[$i] = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            }
            $data[] = [
                'rank' => $rank,
                'lvl'  => $lvl,
                'elvl' => $elvl,
                'weights' => $weights,
            ];
        }
        file_put_contents('/data/php/games/zhanhd/cache/zombie.data.php', '<?php return '.var_export($data, true).';');
    }

    /* world boss handler */
    public function worldboss_handler($excel, $sheet = '世界BOSS')
    {
        printf("TRUNCATE `zhanhd.config`.`WorldBoss`;\n");
        printf("TRUNCATE `zhanhd.config`.`WorldBossDrop`;\n");
        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($id = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $ehc = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`WorldBoss` VALUES (%d, %d);\n", $id, $ehc);

            for ($col = 2; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
                $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
                printf("INSERT INTO `zhanhd.config`.`WorldBossDrop` VALUES (%d, %d, %d);\n", $id, $eid, $num);
            }
        }
    }

    /* message template handler */
    public function messageTemplate_handler($excel, $sheet = '邮件模板')
    {
        printf("TRUNCATE `zhanhd.config`.`MessageTemplate`;\n");
        $sheet = $excel->getSheetByName($sheet);
        for ($row = 2; null != ($type = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
            $title = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
            $content = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
            printf("INSERT INTO `zhanhd.config`.`MessageTemplate` VALUES (%d, '%s', '%s');\n", $type, $title, $content);
        }
    }
}

$parser = new AttachConfigParser($argvs->excel, $argvs->calls);
$parser->exec();
