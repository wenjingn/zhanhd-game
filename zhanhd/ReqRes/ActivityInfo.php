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
use System\ReqRes\Set,
    System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Config\Activity,
    Zhanhd\ReqRes\ActivityMetaInfo;

/**
 *
 */
class ActivityInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('instances', new Set(new ActivityMetaInfo));
        $this->attach('recruits',  new Set(new ActivityMetaInfo));
    }

    /**
     * @param Object  $plans
     * @param integer $current
     * @void
     */
    public function fromActivitySet($plans, $current)
    {
        $activities = [];
        foreach ($plans as $o) {
            if (null === ($activity = Store::get('activity', $o->aid))) {
                continue;
            }

            switch ($activity->type) {
            case Activity::TYPE_INSTANCE:
                $activities['instances'][] = $o;
                break;
            case Activity::TYPE_RECRUIT:
                $activities['recruits'][] = $o;
                break;
            }
        }
       
        foreach ($activities as $type => $objects) {
            $this->$type->resize(count($objects)); 
            foreach ($this->$type as $i => $o) {
                $o->aid->intval($objects[$i]->id);
                $o->remain->intval($current < $objects[$i]->begin ? $objects[$i]->begin - $current : 0);
            }
        }
    }
}
