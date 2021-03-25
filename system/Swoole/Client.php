<?php
/**
 * $Id$
 */

/**
 *
 */
namespace System\Swoole;

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use System\ReqRes\ReqResInterface,
    System\ReqRes\Box,
    System\ReqRes\Str,
    System\ReqRes\Int\U08,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
final class Client extends Box
{
    /**
     * @var integer
     */
    const FLAG_CLOSING = 1;
    const FLAG_MONITOR = 2;

    /**
     * @var mixed
     */
    public $local = null,
           $reply = null,
           $qhead = null,
           $qargv = null;

    /**
     *
     * @param  integer $bulkmaxsize
     * @return integer
     */
    public function processInputBuffer($bulkmaxsize)
    {
        // don't parse 'header' until query buffer is big enough
        if (($minlen = $this->qhead->length()) > ($buflen = $this->query->strlen())) {
            return $minlen - $buflen;
        }

        // validate query length
        if (($length = $this->qhead->decode($this->query->strval())->bulklen->intval()) < $minlen ||
            ($bulkmaxsize > 0 && $length > $bulkmaxsize)) {
            return -1;
        }

        // buffer not enough
        if ($length > $buflen) {
            return $length - $buflen;
        }

        // parse arguments if needed
        if ($length > $minlen) {
            $this->qargv = $this->query->substr($minlen, $length - $minlen);
        }

        // reset buffer
        $this->query->strcbk(function($query) use ($length, $buflen) {
            if ($length == $buflen) {
                return '';
            }

            return substr($query, $length);
        });

        // process command
        return 0;
    }

    /**
     *
     * @param  ReqResInterface $o
     * @return void
     */
    public function addReply(ReqResInterface $o)
    {
        $this->reply->set(null, $o);
    }

    /**
     *
     * @param  ReqResInterface $o
     * @return void
     */
    public function binding(ReqResInterface $o)
    {
        $this->attach('mixed', $o);
    }

    /**
     *
     * @return void
     */
    public function reset()
    {
        //$this->local->purge();
        $this->reply->purge();
        $this->qhead->reload();
        $this->qargv = '';
    }

    /**
     *
     * @return string
     */
    public function toString()
    {
        return sprintf('addr=%s port=%d fd=%d uid=%d pid=%d qbuf=%d qlen=%d cmd=%d',
            $this->host->strval(),
            $this->port->intval(),
            $this->sock->intval(),

            $this->uid->intval(),
            $this->login->intval(),
            $this->query->strlen(),
            $this->qhead->bulklen->intval(),
            $this->qhead->command->intval()
        );
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->local = new Object;
        $this->reply = new Object;
        $this->qhead = new ReqResHeader;
        $this->qargv = '';

        $this->attach('sock', new U16);
        $this->attach('host', new Str);
        $this->attach('port', new U16);
        $this->attach('from', new U16);
        $this->attach('work', new U08);
        
        $this->attach('flags', new U08);
        $this->attach('zone',  new U16);
        $this->attach('uid',   new U64);
        $this->attach('login', new U64);
        $this->attach('query', new Str(new U16));
        $this->attach('mixed', new U08);
    }
}
