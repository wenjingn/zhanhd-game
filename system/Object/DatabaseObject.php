<?php
/**
 * $Id$
 */

/**
 *
 */
namespace System\Object;

/**
 *
 */
use System\Stdlib\Object,
    System\Stdlib\PhpPdo,
    System\Stdlib\SharedResourceTrait;

/**
 *
 */
use stdclass,
    PdoStatement;

/**
 *
 */
abstract class DatabaseObject
{
    /**
     *
     */
    use SharedResourceTrait;

    /**
     * @var Object
     */
    private $object = null;

    /**
     * @var boolean
     */
    private $insert = null;

    /**
     * @var mixed
     */
    protected $phppdo = null,
              $ustime = null;

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->phppdo = $this->retrieveResource('pdo',    'globals');
        $this->ustime = $this->retrieveResource('ustime', 'globals');

        $this->insert = true;
        $this->object = new Object;

        foreach ($this->columns() as $c => $v) {
            $this->object->set($c, array(
                'object'  => $v,
                'updated' => false,
            ));
        }

        $this->initial();
    }

    /**
     * @param mixed $c
     * @param mixed $v
     * @return void
     */
    public function daemonSet($c, $v)
    {
        if (null === ($o = $this->object->get($c))) {
            return;
        } else if (false === $this->insert && array_key_exists($c, $this->primary())) {
            return;
        } else if ($o->object === $v) {
            return;
        }

        $o->object  = $v;
        $o->updated = false;
    }

    /**
     *
     * @param  mixed $c
     * @param  mixed $v
     * @return void
     */
    public function __set($c, $v)
    {
        if (null === ($o = $this->object->get($c))) {
            return;
        } else if (false === $this->insert && array_key_exists($c, $this->primary())) {
            return;
        } else if ($o->object === $v) {
            return;
        }

        $o->object  = $v;
        $o->updated = true;
    }

    /**
     *
     * @param  mixed $c
     * @return mixed
     */
    public function __get($c)
    {
        if (null === ($o = $this->object->get($c))) {
            return;
        }

        return $o->object;
    }

    /**
     *
     * @param  integer $from
     * @param  boolean $returnInSecond
     * @return integer
     */
    final public function elapsedTime($from, $returnInSecond = false)
    {
        if ($returnInSecond) {
            return (integer) (($this->ustime - $from) / 1000000);
        }

        return $this->ustime - $from;
    }

    /**
     *
     * @return string
     */
    public function getSelectColumns()
    {
        return $this->object->keys()->map(function($c) {
            return "`$c`";
        })->join(',');
    }

    /**
     *
     * @param  stdclass $object
     * @return void
     */
    public function fromObject(stdclass $object)
    {
        foreach ($this->object as $c => $o) {
            if (false === isset($object->$c)) {
                continue;
            }

            $o->object = $object->$c;
        }

        $this->insert = false;
    }

    /**
     *
     * @param  array $argv
     * @return boolean
     */
    public function find( ... $argv)
    {
        if (count($this->primary()) <> count($argv)) {
            return false;
        }

        return $this->findBySql(sprintf('SELECT %s FROM %s WHERE %s LIMIT 1',
            $this->getSelectColumns(),
            $this->schema(),
            (new Object)->import($this->primary())->keys()->map(function($c) {
                return "`$c` = ?";
            })->join(' AND ')
        ), $argv);
    }

    /**
     *
     * @return integer
     */
    public function save()
    {
        $rowCount = 0;
        if ($this->insert) {
            $this->preInsert();
        } else {
            $this->preUpdate();
        }

        $bind = new Object;
        foreach ($this->object as $c => $o) {
            if ($this->insert || $o->updated) {
                $bind->set($c, $o->object);
            }
        }

        if ($bind->count()) {
            if ($this->insert) {
                $rowCount = $this->phppdo->insert($this->schema(), $bind);
            } else {
                $rowCount = $this->phppdo->update($this->schema(), $bind, $this->buildCondition());
            }
        }

        if ($this->insert) {
            $this->postInsert();
        } else {
            $this->postUpdate();
        }

        $this->insert = false;
        foreach ($this->object as $o) {
            $o->updated = false;
        }

        return $rowCount;
    }

    /**
     *
     * @return integer
     */
    public function drop()
    {
        if ($this->insert) {
            return 0;
        }

        $this->preDelete();
        $rowCount = $this->phppdo->delete($this->schema(), $this->buildCondition());

        $this->postDelete();
        $this->insert = true;

        return $rowCount;
    }

    /**
     *
     * @param  PhpPdo      $pdo
     * @param  string      $class
     * @param  string      $sql
     * @param  array       $bind
     * @param  boolean     $postSelect
     * @param  string|null $index
     * @return Object
     */
    public static function buildSet(PhpPdo $pdo, $class, $sql, array $bind = [], $postSelect = false, $index = null)
    {
        $sets = new Object;
        $bind = array_values($bind);

        if (null === ($stmt = static::fetchStatement($sql, $bind))) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($bind);
        }

        while (($o = $stmt->fetchObject())) {
            $x = new $class;
            $x->fromObject($o);

            if ($postSelect) {
                $x->postSelect();
            }

            if ($index) {
                $sets->set($o->$index, $x);
            } else {
                $sets->set(null,       $x);
            }

            if ($stmt instanceof PdoStatement) {
                static::cacheObject($o, $sql, $bind);
            }
        }

        return $sets;
    }

    /**
     *
     * @return Object
     */
    private function buildCondition()
    {
        return (new Object)->import($this->primary())->map(function($v, $c) {
            return $this->object->$c->object;
        });
    }

    /**
     *
     * @param  string $sql
     * @param  array  $bind
     * @return boolean
     */
    protected function findBySql($sql, array $bind = [])
    {
        $bind = array_values($bind);

        if (null === ($stmt = static::fetchStatement($sql, $bind))) {
            $stmt = $this->phppdo->prepare($sql);
            $stmt->execute($bind);
        }

        if (($o = $stmt->fetchObject())) {
            $this->fromObject($o);
            $this->postSelect();

            if ($stmt instanceof PdoStatement) {
                static::cacheObject($o, $sql, $bind);
            }

            return true;
        }

        return false;
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {}

    /**
     *
     * @return void
     */
    protected function postSelect()
    {}

    /**
     *
     * @return void
     */
    protected function preInsert()
    {}

    /**
     *
     * @return void
     */
    protected function postInsert()
    {}

    /**
     *
     * @return void
     */
    protected function preUpdate()
    {}

    /**
     *
     * @return void
     */
    protected function postUpdate()
    {}

    /**
     *
     * @return void
     */
    protected function preDelete()
    {}

    /**
     *
     * @return void
     */
    protected function postDelete()
    {}

    /**
     *
     * @param  string $sql
     * @param  array  $bind
     * @return mixed
     */
    protected static function fetchStatement($sql, array $bind = [])
    {}

    /**
     *
     * @param  stdclass $object
     * @param  string   $sql
     * @param  array    $bind
     * @return void
     */
    protected static function cacheObject(stdclass $object, $sql, array $bind = [])
    {}

    /**
     *
     * @return string
     */
    abstract public function schema();

    /**
     *
     * @return array
     */
    abstract public function columns();

    /**
     *
     * @return array
     */
    abstract public function primary();
}
