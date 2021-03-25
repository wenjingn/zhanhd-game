<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\User;

/**
 *
 */
use System\Object\DatabaseProfile;

/**
 *
 */
class Profile extends DatabaseProfile
{
    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.global`.`UserProfile`';
    }

    /**
     *
     * @param  integer $uid
     * @return Profile
     */
    public function setUserId($uid)
    {
        $this->where->uid = $uid;
        return $this;
    }
}
