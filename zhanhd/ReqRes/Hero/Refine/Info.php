<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Hero\Refine;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U64;

/**
 *
 */
class Info extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('peid', new U64);
        $this->attach('str', new U16);
        $this->attach('int', new U16);
        $this->attach('stm', new U16);
        $this->attach('dex', new U16);
    }

    /**
     * @param PlayerEntityRefine $refine
     * @return void
     */
    public function fromRefineObject($refine)
    {
        $this->peid->intval($refine->getPlayerEntityId());
        $this->str->intval($refine->str);
        $this->int->intval($refine->int);
        $this->stm->intval($refine->stm);
        $this->dex->intval($refine->dex);
    }
}
