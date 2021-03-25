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
use System\Swoole\ReqResHeader,
    System\ReqRes\Str,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Config\Store;

/**
 *
 */
class ErrorResponse extends ReqResHeader
{
    /**
     * @var string
     */
    public $error = null;

    /**
     *
     * @return ErrorResponse
     */
    public function error($fmt, ... $argv)
    {
        $this->error = vsprintf($fmt, $argv);
        if (Store::has('error', $this->error)) {
            $this->errno->intval(Store::get('error', $this->error));
        } else {
            $this->errno->intval(0);
        }

        return $this;
    }

    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(1);

        $this->attach('errno', new U32);
        // $this->attach('error', new Str);
    }
}
