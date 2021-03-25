<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client;

/**
 *
 */
use Zhanhd\ReqRes\QA\Request,
    Zhanhd\ReqRes\QA\Response,
    Zhanhd\Config\Store,
    Zhanhd\Object\Player\Counter\Cycle as PlayerCounterCycle,
    Zhanhd\Extension\Question\Module   as QuestionModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p      = $c->local->player;
    $version= $request->version->intval();
    $qid    = $request->qid->intval();
    $answer = $request->answer->intval();
    
    /* check */
    if (false === Store::has('question', $qid)) {
        return $c->addReply($this->errorResponse->error('notfound question'));
    }
    
    if ($answer < 1 || $answer > 4) {
        return $c->addReply($this->errorResponse->error('invalid range'));
    }
    
    if ($version != $this->date) {
        $r = new Response;
        $r->version->intval($this->date);
        $questions = QuestionModule::fetch($this->redis, $this->date);
        $r->questions->resize(count($questions));
        foreach ($r->questions as $i => $o) {
            $o->intval($questions[$i]);
        }
        $c->addReply($this->errorResponse->error('expire questions'));
        return $c->addReply($r);
    }
    
    if ($p->counterCycle->qa == PlayerCounterCycle::DAILY_QA_LIMIT) {
        return $c->addReply($this->errorResponse->error('already complete qa'));
    }

    $questions = QuestionModule::fetch($this->redis, $this->date);
    $idx = array_search($qid, $questions);
    if ($idx != $p->counterCycle->qa) {
        return $c->addReply($this->errorResponse->error('invalid qa sequence'));
    }

    /**
     * do it
     */
    $p->counterCycle->qa++;

    if ($correct = ($answer == Store::get('question', $qid)->answer)) {
        $p->counterCycle->qaCorrect++;
    }
    $p->counterCycle->qaRecords |= $correct << $idx;
    $p->counterCycle->save();

    $r = new Response;
    $r->version->intval($this->date);
    $r->right->intval($correct);
    $r->questionInfo->fromPlayerObject($p);
    $c->addReply($r);
};
