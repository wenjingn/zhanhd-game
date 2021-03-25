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
trait ConfigurationTrait
{
    /**
     * @var array
     */
    private static $maps = null;

    /**
     * @var Object
     */
    private static $cache = null;

    /**
     *
     * @return void
     */
    public static function setupMaps(array $maps)
    {
        static::$maps = $maps;
    }

    /**
     *
     * @param  string $schema
     * @param  array  $argv
     * @return mixed
     */
    public function getConfig($schema, ... $argv)
    {
        if (false === array_key_exists($schema, static::$maps)) {
            return;
        }

        if (static::$cache === null) {
            static::$cache = new Object;
        }

        if (null === ($box = static::$cache->get($schema))) {
            $box = static::$cache->set($schema, new Object)->get($schema);
        }

        $key = json_encode($argv);
        if (null === ($o = $box->get($key))) {
            $c = static::$maps[$schema];
            $o = $box->set($key, new $c)->get($key);
            $o->find( ... $argv);
        }

        return $o;
    }
}
