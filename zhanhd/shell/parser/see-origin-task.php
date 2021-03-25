<?php
/**
 * $Id$
 */

/**
 *
 */
$json = json_decode(substr(file_get_contents(getlongopt(['json' => false])['json']), 3));

/**
 *
 */
do {
    echo ">";
    $argvs = trim(fgets(STDIN));
    $argvs = preg_split('/\s+/', $argvs);
    $command = array_shift($argvs);
    if (function_exists($command)) {
        $command($json, ...$argvs);
    }
} while (true);

function dynasty($json, $did = null)
{
    if (empty($did)) {
        foreach ($json as $dynasty) {
            displayDynasty($dynasty);
        }

        return;
    }
    $o = $json[$did-1];
    displayDynasty($o);
}

function battle($json, $did, $bid = null)
{
    if (empty($bid)) {
        foreach ($json[$did-1]->battles as $battle) {
            displayBattle($battle);
        }
        return;
    }

    $o = $json[$did-1]->battles[$bid-1];
    displayBattle($o);
}

function fight($json, $did, $bid, $fid = null)
{
    if (empty($fid)) {
        foreach ($json[$did-1]->battles[$bid-1]->fights as $fight) {
            displayFight($fight);
        }
        return;
    }
    $o = $json[$did-1]->battles[$bid-1]->fights[$fid-1];
    displayFight($o);
}

function event($json, $did, $bid, $fid, $eid = null)
{
    if (empty($eid)) {
        foreach ($json[$did-1]->battles[$bid-1]->fights[$fid-1]->events as $event) {
            displayEvent($event, 0);
        }
        return;
    }
    $o = $json[$did-1]->battles[$bid-1]->fights[$fid-1]->events[$eid-1];
    displayEvent($o, 1);
}


function displayDynasty($o)
{
    foreach ($o as $k => $v) {
        if ($k == 'battles') {
            echo "$k : -\t";
            continue;
        }
        echo "$k : $v\t";
    }

    echo "\n";
}

function displayBattle($o)
{
    foreach ($o as $k => $v) {
        if ($k == 'fights') {
            echo "$k : -\t";
            continue;
        }
        echo "$k : $v\t";
    }

    echo "\n";
}

function displayFight($o)
{
    foreach ($o as $k => $v) {
        if ($k == 'events') {
            echo "$k : -\t";
            continue;
        }

        echo "$k : $v\t";
    }
    echo "\n";
}

function displayEvent($o, $verbose)
{
    if ($verbose) {
        print_r($o);
    } else {
        foreach ($o as $k => $v) {
            if (is_array($v)) {
                echo "$k : -  ";
                continue;
            }

            echo "$k : $v  ";
        }
        echo "\n";
    }
}
