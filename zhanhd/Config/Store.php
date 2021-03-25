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
use stdClass;

/**
 *
 */
class Store
{
    /**
     * @map
     * 
     * @army       兵种
     * @building   建筑
     * @crusade    讨伐
     * @enhance    武将强化
     * @entity     物品
     * @edrop      装备掉落
     * @egroup     装备分组
     * @formation  阵型
     * @goods      商品
     * @heroexp    英雄经验
     * @skill      技能
     * @task       任务
     */

    /**
     * @const integer
     */
    const EPOCH = 1467561600; /* 2016-7-4 */

    /**
     * @var object
     */
    private static $storage;

    /**
     * @return void
     */
    public static function setup(stdClass $o)
    {
        self::$storage = $o;
    }

    /**
     * @param string $table
     * @param integer|null $id
     * @return array|object
     */
    public static function get($table, $id = null)
    {
        if (false === isset(self::$storage->$table)) {
            return null;
        }

        $o = self::$storage->$table;
        if ($id === null) {
            return $o;
        }

        if (false === isset($o[$id])) {
            return null;
        }
        return $o[$id];
    }

    /**
     * @param string $table
     * @param integer|null $id
     * @return boolean
     */
    public static function has($table, $id = null)
    {
        if (false === isset(self::$storage->$table)) {
            return false;
        }

        if ($id === null) {
            return true;
        }

        return array_key_exists($id, self::$storage->$table);
    }

    /**
     * @return void
     */
    public static function debug()
    {
        foreach (self::$storage as $k => $ignore) {
            printf("%s\n", $k);
        }
    }
}
