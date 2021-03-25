<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config;

/**
 *
 */
use System\Object\ConfigObject;

/**
 *
 */
class Instance extends ConfigObject
{
    /**
     * @struct self
     * @id     integer
     * @prev   integer
     * @next   integer
     * @energy integer
     * @events [ (integer)evt => Instance\Event ]
     */

    /**
     * @const integer
     */
    const INIT   = 10101;
    const UNLOCK = 10505;

    /**
     * @const integer
     */
    const DIFF_NORMAL = 1;
    const DIFF_HARD   = 2;
    const DIFF_CRAZY  = 3;

    /**
     * @param integer $evt
     * @return boolean
     */
    public function hasEvent($evt)
    {
        return isset($this->events[$evt]);
    }

    /**
     * @param integer $evt
     * @return Instance\Event
     */
    public function getEvent($evt)
    {
        return isset($this->events[$evt]) ? $this->events[$evt] : null;
    }

    /**
     * @param integer $evt
     * @return string
     */
    public function getEventCounterKey($evt)
    {
        return sprintf('task-%d-%d', $this->id*100 + $evt, $this->diff);
    }

    /**
     * @param string
     */
    public function getCounterKey()
    {
        return sprintf('fight-%d-%d', $this->id, $this->diff);
    }

    /**
     * @return array
     */
    public function getAllPath()
    {
        $curr = 1;
        $ret = [];
        $path = [];
        $traversed = [];

        while ($curr) {
            $path[] = $curr;
            $event = $this->getEvent($curr);
            if (isset($event->next)) {
                $branchTraversed = true;
                foreach ($event->next as $next => $ignore) {
                    if (false === isset($traversed[$event->evt]) || false === isset($traversed[$event->evt][$next])) {
                        $branchTraversed = false;
                        $curr = $next;
                        $traversed[$event->evt][$next] = true;
                        break;
                    }
                }

                if ($branchTraversed) {
                    unset($traversed[$event->evt]);
                    array_pop($path);
                    $curr = array_pop($path);
                }
            } else {
                $ret[] = $path;
                array_pop($path);
                $curr = array_pop($path);
            }
        }

        return $ret;
    }
}
