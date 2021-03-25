<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Store\Prop\Batch;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32,
	System\ReqRes\Int\U08;

/**
 *
 */
class Request extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('gid', new U32);
		$this->attach('num', new U08);
    }
}
