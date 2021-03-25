<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Str,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\Object\Message;

/**
 *
 */
class MessageMailInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('id',      new U64);

        $this->attach('time',    new U32);
        $this->attach('title',   new Str);
        $this->attach('content', new Str);
    }

    /**
     * @return void
     */
    public function fromMessageObject(Message $o)
    {
        $this->id->intval($o->id);
        $this->time->intval((integer)($o->created / 1000000));
        $this->title->strval($o->getTitle());
        $this->content->strval($o->getContent());
    }
}
