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
    System\ReqRes\Set,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Player,
    Zhanhd\Config\Entity as SourceEntity;

/**
 *
 */
class IllustrationResponse extends ReqResHeader
{
    /**
     *
     * @return IllustrationResponse
     */
    public function fromPlayerObject(Player $p)
    {
        $pis = $p->getIllustration()->filter(function($o) {
            return (boolean) ($o->type == SourceEntity::TYPE_HERO);
        }, true);

        $this->eids->resize($pis->count());
        foreach ($this->eids as $i => $x) {
            $x->intval($pis->get($i)->eid);
        }

        return $this;
    }

    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(52);
        $this->attach('eids', new Set(new U32));
    }
}
