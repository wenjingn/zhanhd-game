<?php
/**
 * $Id$
 */

/**
 *
 */
namespace System\ReqRes;

/**
 *
 */
interface ReqResInterface
{
    /**
     *
     * @return string
     */
    public function encode();

    /**
     *
     * @param  string  $packed
     * @param  integer $offset
     * @return ReqResInterface
     */
    public function decode($packed, $offset = 0);

    /**
     *
     * @return integer
     */
    public function length();

    /**
     *
     * @return ReqResInterface
     */
    public function reload();

    /**
     *
     * @return array
     */
    public function export();
}
