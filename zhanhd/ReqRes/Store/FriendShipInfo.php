<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Store;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\Extension\FriendShipStore\Module;

/**
 *
 */
class FriendShipInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('version', new U32);
        $this->attach('goods', new Set(new U32));
    }

    /**
     * @return void
     */
    public function fromGlobalObject($global)
    {
        $this->version->intval($global->date);

        $goods = Module::fetch($global->redis, $global->date);
        $this->goods->resize(count($goods));
        foreach ($this->goods as $i => $o) {
            $o->intval($goods[$i]);
        }
    }
}
