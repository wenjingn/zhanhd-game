<?php
/**
 * $Id$
 */

/**
 *
 */
require '/data/php/games/zhanhd/shell/bootstrap.php';
require '/data/php/library/phpexcel/PHPExcel.php';

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Object\Player\Reward as PlayerReward;

/**
 *
 */
$argv = (new Object)->import(getlongopt([
    'excel' => false,            
]));

/**
 *
 */
$excel = PHPExcel_IOFactory::load($argv->excel);
$sheet = $excel->getSheet(0);

/**
 *
 */
$created = ustime();
for ($row = 2; null != ($time = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
    $pr = new PlayerReward;
    $pr->flags = 1;
    $sendtime = strtotime($time)*1000000;
    $pr->sendtime = $sendtime;
    $pr->expire   = $sendtime + 86399*1000000;
    $pr->from = PlayerReward::from_gm;
    $pr->profile->title   = $sheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
    $pr->profile->content = $sheet->getCellByColumnAndRow(2, $row)->getCalculatedValue();
    
    for ($col = 3; null != ($eid = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue()); $col += 2) {
        $num = $sheet->getCellByColumnAndRow($col+1, $row)->getCalculatedValue();
        $pr->source->$eid = $num;
    }
    $pr->save();
}
