<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\QA;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U16;

/**
 *
 */
use Zhanhd\ReqRes\QuestionInfo;

/**
 *
 */
class Response extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(102);
        $this->attach('version',      new U32);
        $this->attach('right',        new U16);
        $this->attach('questionInfo', new QuestionInfo);
        $this->attach('questions',    new Set(new U16));
    }
}
