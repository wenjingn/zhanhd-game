<?php
/**
 * $Id$
 */

/**
 *
 */
require '/data/php/games/zhanhd/shell/bootstrap.php';

/**
 *
 */
function test()
{
    new Zhanhd\ReqRes\Rob\Target;
    new Zhanhd\ReqRes\Rob\Log;
}

function testRewardMail($g)
{
    $pr = new Zhanhd\Object\Player\Reward;
    $pr->find(1118);
    $info = new Zhanhd\ReqRes\RewardMailInfo;
    $info->fromPlayerRewardObject($pr, $g);
    print_r($info);
}

function testRevengeRes()
{
    $r = new Zhanhd\ReqRes\Rob\Revenge\Response;
    $r->decode(file_get_contents(getlongopt(['file'=>false])['file']));
    print_r($r);
}
testRevengeRes();
