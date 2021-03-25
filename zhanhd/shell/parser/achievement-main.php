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
require '/data/php/library/phpexcel/PHPExcel.php';

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
$argv = (new Object)->import(getlongopt([
    'excel' => false,            
]));

define('COL_ID', 0);
define('COL_TAG', 1);
define('COL_TYPE', 2);
define('COL_FIGHT', 5);
define('COL_EID', 6);
define('COL_NUM', 7);
define('COL_UNLOCK', 8);

$ENUMS_UNLOCK = [
    0 => '',
    1 => 'mall',
    2 => 'crusade',
    3 => 'building',
    4 => 'enhance',
    5 => 'task-active',
    6 => 'pvp',
];

/**
 * free store
 */
printf("delete `as`.* from `zhanhd.config`.`AchievementSource` `as` left join `zhanhd.config`.`Achievement` `a` on (`as`.`aid` = `a`.`id`)  where `a`.`type` = 1;\n");
printf("delete from `zhanhd.config`.`Achievement` where type = 1;\n");

/**
 *
 */
$excel = PHPExcel_IOFactory::load($argv->excel);
$sheet = $excel->getSheetByName('mission');

$row = 2;
while (true) {
    if (empty($id = $sheet->getCellByColumnAndRow(COL_ID, $row)->getCalculatedValue())) {
        break;
    }
    
    if ($sheet->getCellByColumnAndRow(COL_TYPE, $row)->getCalculatedValue() != 1) {
        break;
    }

    $tag = $sheet->getCellByColumnAndRow(COL_TAG, $row)->getCalculatedValue();
    $fight = (integer)$sheet->getCellByColumnAndRow(COL_FIGHT, $row)->getCalculatedValue();

    $unlock = (integer)$sheet->getCellByColumnAndRow(COL_UNLOCK, $row)->getCalculatedValue();
    printf("INSERT INTO `zhanhd.config`.`Achievement` VALUES (%d, '%s', 1, 'task', '%s', 0, 'normal', '%s');\n",
        $id, $tag, $fight, $ENUMS_UNLOCK[$unlock]
    );
    
    $eid = $sheet->getCellByColumnAndRow(COL_EID, $row)->getCalculatedValue();
    $num = $sheet->getCellByColumnAndRow(COL_NUM, $row)->getCalculatedValue();
    printf("INSERT INTO `zhanhd.config`.`AchievementSource` VALUES (%d, %d, %d);\n",
        $id, $eid, $num
    );

    $row++;
}
