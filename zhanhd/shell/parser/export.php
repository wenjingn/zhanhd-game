<?php
/**
 * $Id$
 */

/**
 *
 */
(new PsrAutoloader)->register('System', '/data/php/games/system');

/**
 *
 */
spl_autoload_register(function($class) {
    if (strncmp($class, 'PHPExcel', 8)) {
        return false;
    }

    $fullpath = sprintf('/data/php/library/phpexcel/%s.php', str_replace('_', '/', $class));
    if (file_exists($fullpath)) {
        include $fullpath;
        return $fullpath;
    }

    return false;
});

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
$argv = (new Object)->import(getlongopt(array(
    'excel' => false,
)));

/**
 *
 */
if (false === file_exists($argv->excel)) {
    throw new Exception("Excel not found");
}

/**
 *
 */
$excel = PHPExcel_IOFactory::load($argv->excel);

/**
 *
 */
$config = $excel->getSheet(0);

/**
 *
 */
printf("truncate `Army`;\n");
printf("truncate `ArmyUpgradation`;\n");
printf("delete `ea`.* from `EntityArmy` `ea` left join `Entity` `e` on (`ea`.eid = `e`.id);\n");
printf("delete `ep`.* from `EntityProperty` `ep` left join `Entity` `e` on (`ep`.eid = `e`.id) where `e`.type = 20;\n");
printf("delete `es`.* from `EntitySkill` `es` left join `Entity` `e` on (`es`.eid = `e`.id);\n");
printf("delete from `Entity` where type = 20;\n");
printf("truncate `Skill`;\n");
printf("truncate `SkillEffect`;\n");
$col = 0; $row = 1; $eof = false; while (false === $eof) {
    $group = $config->getCellByColumnAndRow($col++, $row)->getCalculatedValue();
    $sheet = $config->getCellByColumnAndRow($col++, $row)->getCalculatedValue();

    if (empty($group) && empty($sheet)) {
        $eof = true;
        break;
    }

    call_user_func(sprintf('%s_handler', str_replace('-', '_', $group)), $excel->getSheet($sheet));
    $col = 0;
    $row++;
}

//============================== callbacks ==============================//

/**
 *
 * @param PHPExcel_Worksheet $sheet
 * @return void
 */
function army_handler(PHPExcel_Worksheet $sheet)
{
    $row = 2; $col = 0; $eof = false; while (false === $eof) {
        if (empty(($id = $sheet->getCellByColumnAndRow($col++, $row)->getCalculatedValue()))) {
            $eof = true;
            break;
        }

        printf("REPLACE INTO `Army` VALUES (%d,\47%s\47,%d,%d,%d,%d,%d,%d,%d,%d);\n",
            $id,
            $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(9, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(6, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(7, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(8, $row)->getCalculatedValue()
        );

        for ($col = 10; null !== ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col+=2) {
            if (empty($eid)) {
                break;
            }
            $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
            printf("REPLACE INTO `ArmyUpgradation` VALUES (%d, %d, %d);\n", $id, $eid, $num);
        }

        $col = 0;
        $row++;
    }
}

/**
 *
 * @param PHPExcel_Worksheet $sheet
 * @return void
 */
function entity_20_handler(PHPExcel_Worksheet $sheet)
{
    for ($row = 2, $col = 0; null != ($id = $sheet->getCellByColumnAndRow($col++, $row)->getCalculatedValue()); $row++, $col=0) {
        $diffrace = (integer)$sheet->getCellByColumnAndRow(31, $row)->getCalculatedValue();

        printf("REPLACE INTO `Entity` VALUES (%d,\47%s\47,%d,%d,%d,%d,%d,%d,%d);\n",
            $id,
            $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue(),
            20,
            $sheet->getCellByColumnAndRow(8, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue(),
            0, 0, $diffrace, 0
        );

        for ($col = 12; $col <= 23;) {
            $k = $sheet->getCellByColumnAndRow($col++, $row)->getCalculatedValue();
            $v = $sheet->getCellByColumnAndRow($col++, $row)->getCalculatedValue();

            if ($k && $v) {
                printf("REPLACE INTO `EntityArmy` VALUES (%d,%s,%d);\n", $id, $v, $k);
            }
        }

        foreach (array(
            'strmin' => ['strmax', 'strext', 3],
            'intmin' => ['intmax', 'intext', 4],
            'stmmin' => ['stmmax', 'stmext', 5],
            'dexmin' => ['dexmax', 'dexext', 6],
        ) as $k => list($max, $ext, $c)) {
            $v = $sheet->getCellByColumnAndRow($c, $row)->getCalculatedValue();
            printf("REPLACE INTO `EntityProperty` VALUES (%d,'%s',%d);\n",
                $id,
                $k,
                $v
            );

            printf("REPLACE INTO `EntityProperty` VALUES (%d,'%s',%d);\n",
                $id,
                $max,
                $v + 20
            );

            if (($v = $sheet->getCellByColumnAndRow(7, $row)->getCalculatedValue())) {
                printf("REPLACE INTO `EntityProperty` VALUES (%d,'%s',%d);\n",
                    $id,
                    $ext,
                    $v
                );
            }
        }

        if ($v = $sheet->getCellByColumnAndRow(9, $row)->getCalculatedValue()) {
            printf("REPLACE INTO `EntityProperty` VALUES (%d,'dynasty',%d);\n",
                $id,
                $v
            );
        }

        if ($sheet->getCellByColumnAndRow(10, $row)->getCalculatedValue()) {
            printf("INSERT INTO `EntityProperty` VALUES (%d, 'npc', 1);\n", $id);
        }

        $x = [1, 30, 60, 80, 100, 120]; for ($col = 24, $i = 0; $col <= 29; $i++) {
            if (($k = $sheet->getCellByColumnAndRow($col++, $row)->getCalculatedValue())) {
                printf("REPLACE INTO `EntitySkill` VALUES (%d,%d,%d);\n", $id, $k, $x[$i]);
            }
        }

    }
}

/**
 *
 * @param PHPExcel_Worksheet $sheet
 * @return void
 */
function skill_1_handler(PHPExcel_Worksheet $sheet)
{
    $row = 2; $col = 0; $eof = false; while (false === $eof) {
        if (empty(($id = $sheet->getCellByColumnAndRow($col++, $row)->getCalculatedValue()))) {
            $eof = true;
            break;
        }

        $random = $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue();
        switch ($sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue()) {
        case "??????": $mode = 'single';     break;
        case "??????": $mode = 'horizontal'; break;
        case "??????": $mode = 'vertical';   break;
        case "??????": $mode = 'full';       break;
        case "??????": $mode = 'random';     break;
        }

        printf("INSERT INTO `Skill` VALUES (%d,'%s',1,'%s',%d,%d,%d,%d,%d,%d,%d,0,0,0,0);\n",
            $id,
            $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue(),
            $mode,
            $random,
            $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(6, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(7, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(8, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(9, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(10, $row)->getCalculatedValue()
        );

        if (
            ($v12 = $sheet->getCellByColumnAndRow(12, $row)->getCalculatedValue()) &&
            ($v13 = $sheet->getCellByColumnAndRow(13, $row)->getCalculatedValue()) &&
            ($v14 = $sheet->getCellByColumnAndRow(14, $row)->getCalculatedValue()) &&
            ($v15 = $sheet->getCellByColumnAndRow(15, $row)->getCalculatedValue()) &&
            ($v16 = $sheet->getCellByColumnAndRow(16, $row)->getCalculatedValue())
        ) {
            switch ($sheet->getCellByColumnAndRow(11, $row)->getCalculatedValue()) {
            case "??????":     $eid = 10009; break;
            case "??????":     $eid = 10010; break;
            case "??????":     $eid = 10011; break;
            case "????????????": $eid = 10026; break;
            }

            printf("INSERT INTO `SkillEffect` VALUES (%d,%d,0,0,0,0,0,0,0,0,%d,%d,%d,%d,%d);\n", $id, $eid, $v12, $v13, $v14, $v15, $v16);
        }

        $col = 0;
        $row++;
    }
}

/**
 *
 * @param PHPExcel_Worksheet $sheet
 * @return void
 */
function skill_2_handler(PHPExcel_Worksheet $sheet)
{
    $row = 2; $col = 0; $eof = false; while (false === $eof) {
        if (empty(($id = $sheet->getCellByColumnAndRow($col++, $row)->getCalculatedValue()))) {
            $eof = true;
            break;
        }

        switch ($sheet->getCellByColumnAndRow(7, $row)->getCalculatedValue()) {
        case "??????": $to=1; break;
        case "??????": $to=2; break;
        }

        switch ($sheet->getCellByColumnAndRow(8, $row)->getCalculatedValue()) {
        case "??????": $at=1; break;
        case "??????": $at=2; break;
        }

        $anti=0; if (($v = $sheet->getCellByColumnAndRow(9, $row)->getCalculatedValue()))
            $anti=1;
        $army=0; if (($v = $sheet->getCellByColumnAndRow(10, $row)->getCalculatedValue()))
            $army=$v;
        $dynasty=0; if (($v = $sheet->getCellByColumnAndRow(11, $row)->getCalculatedValue()))
            $dynasty=$v;
        $diffrace=0; if (($v = $sheet->getCellByColumnAndRow(12, $row)->getCalculatedValue()))
            $diffrace=$v;

        printf(
            "REPLACE INTO `Skill` VALUES (%d,'%s',2,'',0,0,0,0,0,0,0,%d,%d,%d,%d);\n",
            $id,
            $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue()
        );

        if (($v9 = $sheet->getCellByColumnAndRow(13, $row)->getCalculatedValue())) {
            switch ($sheet->getCellByColumnAndRow(14, $row)->getCalculatedValue()) {
            case "??????": $op=1; break;
            case "??????": $op=2; break;
            }

            switch ($v9) {
            case "??????":         $eid=10001; break;
            case "??????":         $eid=10002; break;
            case "??????":         $eid=10003; break;
            case "??????":         $eid=10004; break;
            case "??????":         $eid=10005; break;
            case "???":           $eid=10006; break;
            case "???":           $eid=10007; break;
            case "??????":         $eid=10008; break;
            case "??????":         $eid=10009; break;
            case "??????":         $eid=10010; break;
            case "??????":         $eid=10011; break;
            case "??????":         $eid=10012; break;
            case "???":           $eid=10013; break;
            case "????????????":     $eid=10014; break;
            case "????????????":     $eid=10015; break;
            case "???":           $eid=10016; break;
            case "???%":          $eid=10017; break;
            case "???%":          $eid=10018; break;
            case "???%":          $eid=10019; break;
            case "???%":          $eid=10020; break;
            case "??????/??????":    $eid=10021; break;
            case "??????/??????":    $eid=10022; break;
            case "??????/??????":    $eid=10023; break;
            case "??????????????????": $eid=10024; break;
            case "??????????????????": $eid=10025; break;
            case "????????????":     $eid=10026; break;
            }

            printf("REPLACE INTO `SkillEffect` VALUES (%d,%d,%d,%d,%d,%d,%d,%d,%d,0,%d,%d,%d,%d,%d);\n",
                $id, $eid, $at, $op, $to, $anti, $army, $dynasty, $diffrace,
                $sheet->getCellByColumnAndRow(15, $row)->getCalculatedValue(),
                $sheet->getCellByColumnAndRow(16, $row)->getCalculatedValue(),
                $sheet->getCellByColumnAndRow(17, $row)->getCalculatedValue(),
                $sheet->getCellByColumnAndRow(18, $row)->getCalculatedValue(),
                $sheet->getCellByColumnAndRow(19, $row)->getCalculatedValue()
            );
        }

        if (($v16 = $sheet->getCellByColumnAndRow(20, $row)->getCalculatedValue())) {
            switch ($sheet->getCellByColumnAndRow(21, $row)->getCalculatedValue()) {
            case "??????": $op=1; break;
            case "??????": $op=2; break;
            }

            switch ($v16) {
            case "??????":         $eid=10001; break;
            case "??????":         $eid=10002; break;
            case "??????":         $eid=10003; break;
            case "??????":         $eid=10004; break;
            case "??????":         $eid=10005; break;
            case "???":           $eid=10006; break;
            case "???":           $eid=10007; break;
            case "??????":         $eid=10008; break;
            case "??????":         $eid=10009; break;
            case "??????":         $eid=10010; break;
            case "??????":         $eid=10011; break;
            case "??????":         $eid=10012; break;
            case "???":           $eid=10013; break;
            case "????????????":     $eid=10014; break;
            case "????????????":     $eid=10015; break;
            case "???":           $eid=10016; break;
            case "???%":          $eid=10017; break;
            case "???%":          $eid=10018; break;
            case "???%":          $eid=10019; break;
            case "???%":          $eid=10020; break;
            case "??????/??????":    $eid=10021; break;
            case "??????/??????":    $eid=10022; break;
            case "??????/??????":    $eid=10023; break;
            case "??????????????????": $eid=10024; break;
            case "??????????????????": $eid=10025; break;
            case "????????????":     $eid=10026; break;
            }

            printf("REPLACE INTO `SkillEffect` VALUES (%d,%d,%d,%d,%d,%d,%d,%d,%d,0,%d,%d,%d,%d,%d);\n",
                $id, $eid, $at, $op, $to, $anti, $army, $dynasty, $diffrace,
                $sheet->getCellByColumnAndRow(22, $row)->getCalculatedValue(),
                $sheet->getCellByColumnAndRow(23, $row)->getCalculatedValue(),
                $sheet->getCellByColumnAndRow(24, $row)->getCalculatedValue(),
                $sheet->getCellByColumnAndRow(25, $row)->getCalculatedValue(),
                $sheet->getCellByColumnAndRow(26, $row)->getCalculatedValue()
            );
        }

        $col = 0;
        $row++;
    }
}

/**
 *
 * @param PHPExcel_Worksheet $sheet
 * @return void
 */
function skill_3_handler(PHPExcel_Worksheet $sheet)
{
    $row = 2; $col = 0; $eof = false; while (false === $eof) {
        if (empty(($id = $sheet->getCellByColumnAndRow($col++, $row)->getCalculatedValue()))) {
            $eof = true;
            break;
        }

        switch ($sheet->getCellByColumnAndRow(7, $row)->getCalculatedValue()) {
        case "??????": $to=1; break;
        case "??????": $to=2; break;
        }

        switch ($sheet->getCellByColumnAndRow(8, $row)->getCalculatedValue()) {
        case "??????": $at=1; break;
        case "??????": $at=2; break;
        }

        $anti=0; if (($v = $sheet->getCellByColumnAndRow(9, $row)->getCalculatedValue()))
            $anti=1;
        $army=0; if (($v = $sheet->getCellByColumnAndRow(10, $row)->getCalculatedValue()))
            $army=$v;
        $dynasty=0; if (($v = $sheet->getCellByColumnAndRow(11, $row)->getCalculatedValue()))
            $dynasty=$v;
        $diffrace=0; if (($v = $sheet->getCellByColumnAndRow(12, $row)->getCalculatedValue()))
            $diffrace=$v;

        printf(
            "REPLACE INTO `Skill` VALUES (%d,'%s',3,'',0,0,0,0,0,0,0,%d,%d,%d,%d);\n",
            $id,
            $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(4, $row)->getCalculatedValue(),
            $sheet->getCellByColumnAndRow(5, $row)->getCalculatedValue()
        );

        for ($j = 13; null != ($v9 = $sheet->getCellByColumnAndRow($j, $row)->getCalculatedValue()); $j+=3) {
            switch ($sheet->getCellByColumnAndRow($j+1, $row)->getCalculatedValue()) {
            case "??????": $op=1; break;
            case "??????": $op=2; break;
            }

            switch ($v9) {
            case "??????":         $eid=10001; break;
            case "??????":         $eid=10002; break;
            case "??????":         $eid=10003; break;
            case "??????":         $eid=10004; break;
            case "??????":         $eid=10005; break;
            case "???":           $eid=10006; break;
            case "???":           $eid=10007; break;
            case "??????":         $eid=10008; break;
            case "??????":         $eid=10009; break;
            case "??????":         $eid=10010; break;
            case "??????":         $eid=10011; break;
            case "??????":         $eid=10012; break;
            case "???":           $eid=10013; break;
            case "????????????":     $eid=10014; break;
            case "????????????":     $eid=10015; break;
            case "???":           $eid=10016; break;
            case "???%":          $eid=10017; break;
            case "???%":          $eid=10018; break;
            case "???%":          $eid=10019; break;
            case "???%":          $eid=10020; break;
            case "??????/??????":    $eid=10021; break;
            case "??????/??????":    $eid=10022; break;
            case "??????/??????":    $eid=10023; break;
            case "??????????????????": $eid=10024; break;
            case "??????????????????": $eid=10025; break;
            case "????????????":     $eid=10026; break;
            }

            printf("REPLACE INTO `SkillEffect` VALUES (%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,0,0,0,0,0);\n",
                $id, $eid, $at, $op, $to, $anti, $army, $dynasty, $diffrace,
                $sheet->getCellByColumnAndRow($j+2, $row)->getCalculatedValue()
            );
        }

        $col = 0;
        $row++;
    }
}
