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
use System\ReqRes\Box,
    System\ReqRes\Int\U16;

/**
 *
 */
class ReqResHeader extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('bulklen', new U16);
        $this->attach('command', new U16);

        $this->setupResponse();
    }

    /**
     *
     * @return void
     */
    protected function setupResponse()
    {}

    /**
     *
     * @return ReqResInterface
     */
    protected function finalize()
    {
        $this->bulklen->intval($this->length());
        return $this;
    }
}
