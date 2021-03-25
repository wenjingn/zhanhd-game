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
use Zhanhd\ReqRes\MessageMail\Page\Request,
    Zhanhd\ReqRes\MessageMail\Page\Response;

define('MAIL_PAGE_MAXSIZE', 20);

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    $idx = $request->page->intval();
    $num = $request->num->intval();

    if ($num > MAIL_PAGE_MAXSIZE) {
        $num = MAIL_PAGE_MAXSIZE;
    }

    $page = $p->getMessagePage($idx, $num);
    if ($page->data->count()) {
        $lastMessage = $page->data->get(0);
        if ($lastMessage->id > $p->profile->lastReadMessageId) {
            $p->profile->lastReadMessageId = $lastMessage->id;
            $p->profile->save();
        }
    }

    $r = new Response;
    $r->page->intval($page->index);
    $r->mails->resize($page->data->count());
    foreach ($r->mails as $i => $o) {
        $o->fromMessageObject($page->data->get($i));
    }
    $c->addReply($r);
};
