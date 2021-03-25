<?php
/**
 * $Id$
 */

/**
 *
 */
namespace System\Stdlib;

/**
 *
 */
use stdclass;

/**
 *
 */
trait CacheableStatementTrait
{
    /**
     * @var Object
     */
    private static $cached = null;

    /**
     *
     * @param  string $sql
     * @param  array  $bind
     * @return mixed
     */
    protected static function fetchStatement($sql, array $bind = [])
    {
        if (static::$cached === null) {
            static::$cached = new Object;
        }

        if ($o = static::$cached->get(static::formatCacheKey($sql, $bind))) {
            $o->pointer = 0;
        }

        return $o;
    }

    /**
     *
     * @param  stdclass $object
     * @param  string   $sql
     * @param  array    $bind
     * @return void
     */
    protected static function cacheObject(stdclass $object, $sql, array $bind = [])
    {
        if (static::$cached === null) {
            static::$cached = new Object;
        }

        if (null === ($o = static::$cached->get(($id = static::formatCacheKey($sql, $bind))))) {
            $o = new Object;
            $o->fetchObject = function() use ($o) {
                if (null === ($x = $o->objects->get($o->pointer++))) {
                    return false;
                }

                return $x;
            };

            $o->pointer = 0;
            $o->objects = [];

            static::$cached->set($id, $o);
        }

        $o->objects->set(null, $object);
    }

    /**
     *
     * @param  string $sql
     * @param  array  $bind
     * @return string
     */
    private static function formatCacheKey($sql, array $bind = [])
    {
        return sprintf('-sql-%s-argv-%s-', $sql, implode('-', $bind));
    }
}
