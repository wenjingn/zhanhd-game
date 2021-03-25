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
class ResourceRecruit extends ConfigObject
{
    /**
     * @total integer
     * @prop  integer
     * @prob1 integer
     * @prob2 integer
     * @prob3 integer
     * @prob4 integer
     * @prob5 integer
     */

    /**
     *
     * @return void
     */
    public function pick()
    {
        $r = array(
            'prob1' => 12,
            'prob2' => 3,
            'prob3' => 4,
            'prob4' => 5,
            'prob5' => 10000,
        );

        $seed = 0; foreach ($r as $k => $v) {
            if ($this->$k > 0) {
                $seed += $this->$k;
            }
        }

        if ($seed == 0) {
            return false;
        }

        $rand = mt_rand(1, $seed); foreach ($r as $k => $v) {
            if ($this->$k <= 0) {
                continue;
            }

            $rand -= $this->$k;
            if ($rand > 0) {
                continue;
            }

            return $v;
        }

        return false;
    }
}
