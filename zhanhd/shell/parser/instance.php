<?php
/**
 * $Id$
 */

/**
 *
 */
(new PsrAutoloader)->register('System', '/data/php/games/system');
(new PsrAutoloader)->register('Zhanhd', '/data/php/games/zhanhd');
require '/data/php/library/phpexcel/PHPExcel.php';

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Config\Instance,
    Zhanhd\Config\Instance\Event;

/**
 *
 */
class Parser {
    /**
     * @var array
     */
    private static $EVENT_TYPE_TAB = [
        Event::TYPE_FIGHT,
        Event::TYPE_RESOURCE,
        Event::TYPE_BRANCH,
        Event::TYPE_RANDOM,
        Event::TYPE_FINAL,
    ];

    /**
     * @param integer $diff
     * @param string  $json
     * @param string  $excel
     * @return void
     */
    public function __construct($diff, $json)
    {
        $this->diff = $diff;
        $this->json = json_decode(substr(file_get_contents($json), 3));
        $this->fights = [];
    }

    /**
     * @return void
     */
    public function parse()
    {
        foreach ($this->json as $dynasty) {
            $this->parseDynasty($dynasty);
        }
        foreach ($this->fights as $id => $prop) {
            $prev = isset($prop['prev']) ? $prop['prev'] : 0;
            $next = isset($prop['next']) ? $prop['next'] : 0;
            printf("INSERT INTO `zhanhd.config`.`Instance` VALUES (%d, %d, %d, %d, %d);\n", $this->diff, $id, $prev, $next, 0);
        }
    }

    /**
     * @param stdClass $dynasty
     * @return void
     */
    private function parseDynasty($dynasty)
    {
        foreach ($dynasty->battles as $battle) {
            $this->parseBattle($dynasty->dynastyId, $battle);
        }
    }

    /**
     * @param integer $dynastyId
     * @param stdClass $battle
     * @return void
     */
    private function parseBattle($dynastyId, $battle)
    {
        foreach ($battle->fights as $fight) {
            $this->parseFight($dynastyId*100 + $battle->battleId, $fight);
        }
    }

    /**
     * @param integer $battleId
     * @param stdClass $fight
     * @return void
     */
    private function parseFight($battleId, $fight)
    {
        foreach ($fight->events as $i => $event) {
            $this->parseEvent($battleId*100 + $fight->fightId, $i, $event);
        }
    }

    /**
     * @param integer $fightId
     * @param integer $i
     * @param stdClass $event
     * @return void
     */
    private function parseEvent($fightId, $i, $event)
    {
        $flags = self::$EVENT_TYPE_TAB[$event->eventType];
        if ($i == 0) {
            $flags |= Event::TYPE_HEAD;
        }
        if (isset($event->endFlag) && $event->endFlag) {
            $nextFightId = (int)($event->endFlag/100);
            $this->fights[$fightId]['next'] = $nextFightId;
            $this->fights[$nextFightId]['prev'] = $fightId;
            $flags |= Event::TYPE_TAIL;
        }
        if ($event->eventType == 0) {
            printf("INSERT INTO `zhanhd.config`.`InsEvt` VALUES (%d, %d, %d, %d, %d, %d);\n", $this->diff, $fightId, $event->eventId, $flags, $event->zx, $event->exp);
        } else {
            printf("INSERT INTO `zhanhd.config`.`InsEvt` VALUES (%d, %d, %d, %d, 0, 0);\n", $this->diff, $fightId, $event->eventId, $flags);
        }
        switch ($event->eventType) {
        case 0:
            $this->parseFightEvent($fightId, $event);
            break;
        case 1:
            $this->parseResourceEvent($fightId, $event);
            break;
        case 2:
            $this->parseBranchEvent($fightId, $event);
            break;
        case 3:
            $this->parseRandomEvent($fightId, $event);
            break;
        case 4:
            break;
        }
    }

    /**
     * @param integer $fightId
     * @param stdClass $event
     * @return void
     */
    private function parseFightEvent($fightId, $event)
    {
        if ($event->endFlag == 0) {
            printf("INSERT INTO `zhanhd.config`.`InsEvtNext` VALUES (%d, %d, %d, %d, %d);\n", $this->diff, $fightId, $event->eventId, $event->nextEventId, 1);
            printf("INSERT INTO `zhanhd.config`.`InsEvtPrev` VALUES (%d, %d, %d, %d);\n", $this->diff, $fightId, $event->nextEventId, $event->eventId);
        }
        foreach ($event->heroStances as $npc) {
            printf("INSERT INTO `zhanhd.config`.`InsEvtNpc` VALUES (%d, %d, %d, %d, %d, %d, %d);\n", 
            $this->diff, $fightId, $event->eventId, $npc->pos, $npc->heroid, $event->levelLmt, $event->skillLevelLmt-1);
        }
        foreach ($event->colGoodsUnion as $i => $drop) {
            printf("INSERT INTO `zhanhd.config`.`InsEvtDrop` VALUES (%d, %d, %d, %d, %d, %d);\n",
            $this->diff, $fightId, $event->eventId, $i, $drop->first == 1 ? 1 : 0, $drop->problity);
            foreach ($drop->goods as $dropitem) {
                printf("INSERT INTO `zhanhd.config`.`InsEvtDropSource` VALUES (%d, %d, %d, %d, %d, %d);\n",
                $this->diff, $fightId, $event->eventId, $i, $dropitem->goodsId, $dropitem->num);
            }
        }
    }

    /**
     * @param integer $fightId
     * @param stdClass $event
     * @return void
     */
    private function parseResourceEvent($fightId, $event)
    {
        if ($event->endFlag == 0) {
            printf("INSERT INTO `zhanhd.config`.`InsEvtNext` VALUES (%d, %d, %d, %d, %d);\n", $this->diff, $fightId, $event->eventId, $event->nextEventId, 1);
            printf("INSERT INTO `zhanhd.config`.`InsEvtPrev` VALUES (%d, %d, %d, %d);\n", $this->diff, $fightId, $event->nextEventId, $event->eventId);
        }
        foreach ($event->colHarvestUnion as $i => $drop) {
            printf("INSERT INTO `zhanhd.config`.`InsEvtDrop` VALUES (%d, %d, %d, %d, %d, %d);\n", 
            $this->diff, $fightId, $event->eventId, $i, $drop->first == 1 ? 1 : 0, $drop->problity);
            foreach ($drop->goods as $dropitem) {
                printf("INSERT INTO `zhanhd.config`.`InsEvtDropSource` VALUES (%d, %d, %d, %d, %d, %d);\n",
                $this->diff, $fightId, $event->eventId, $i, $dropitem->goodsId, $dropitem->num);
            }
        }
    }

    /**
     * @param integer $fightId
     * @param stdClass $event
     * @return void
     */
    private function parseBranchEvent($fightId, $event)
    {
        foreach ($event->branches as $nextId) {
            printf("INSERT INTO `zhanhd.config`.`InsEvtNext` VALUES (%d, %d, %d, %d, %d);\n", $this->diff, $fightId, $event->eventId, $nextId, 1);
            printf("INSERT INTO `zhanhd.config`.`InsEvtPrev` VALUES (%d, %d, %d, %d);\n", $this->diff, $fightId, $nextId, $event->eventId);
        }
    }

    /**
     * @param integer $fightId
     * @param stdClass $event
     * @return void
     */
    private function parseRandomEvent($fightId, $event)
    {
        printf("INSERT INTO `zhanhd.config`.`InsEvtNext` VALUES (%d, %d, %d, %d, %d);\n", $this->diff, $fightId, $event->eventId, $event->fightEventId, $event->glFight);
        printf("INSERT INTO `zhanhd.config`.`InsEvtPrev` VALUES (%d, %d, %d, %d);\n", $this->diff, $fightId, $event->fightEventId, $event->eventId);
        printf("INSERT INTO `zhanhd.config`.`InsEvtNext` VALUES (%d, %d, %d, %d, %d);\n", $this->diff, $fightId, $event->eventId, $event->harvestEventId, $event->glHarvest);
        printf("INSERT INTO `zhanhd.config`.`InsEvtPrev` VALUES (%d, %d, %d, %d);\n", $this->diff, $fightId, $event->harvestEventId, $event->eventId);
    }
}

/**
 *
 */
function excelParser($excel, $sheet = 'fight')
{
    $sheet = PHPExcel_IOFactory::load($excel)->getSheetByName($sheet);
    for ($row = 2; null != ($battleId = $sheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()); $row++) {
        $fid = $sheet->getCellByColumnAndRow(3, $row)->getCalculatedValue();
        $id = $battleId * 100 + $fid;

        for ($col = 9; $col < 12; $col++) {
            $diff   = $col - 8;
            $energy = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            printf("UPDATE `zhanhd.config`.`Instance` SET `energy`=%d WHERE `diff`=%d AND `id`=%d;\n", $energy, $diff, $id);
        }
    }
}

/**
 *
 */
$argvs = (new Object)->import(getlongopt([
    'json'  => false,
    'excel' => false,
]));

if ($argvs->json->count() != 3) {
    throw new Exception('input 3 type file');
}

/**
 *
 */
printf("TRUNCATE `zhanhd.config`.`Instance`;\n");
printf("TRUNCATE `zhanhd.config`.`InsEvt`;\n");
printf("TRUNCATE `zhanhd.config`.`InsEvtPrev`;\n");
printf("TRUNCATE `zhanhd.config`.`InsEvtNext`;\n");
printf("TRUNCATE `zhanhd.config`.`InsEvtNpc`;\n");
printf("TRUNCATE `zhanhd.config`.`InsEvtDrop`;\n");
printf("TRUNCATE `zhanhd.config`.`InsEvtDropSource`;\n");

////energy in zhanhd.config don't forget */
foreach ($argvs->json as $diff => $jsonfile) {
    $parser = new Parser($diff+1, $jsonfile);
    $parser->parse();
}
excelParser($argvs->excel);
