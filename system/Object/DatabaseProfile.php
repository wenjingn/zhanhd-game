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
    System\Stdlib\SharedResourceTrait;

/**
 *
 */
use Countable,
    IteratorAggregate,
    PdoStatement,
    stdclass;

/**
 *
 */
abstract class DatabaseProfile implements Countable, IteratorAggregate
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
     * @var Object
     */
    protected $where = null;

    /**
     * @var Object
     */
    protected $oper = null;

    /**
     * @var string
     */
    protected $kColumn = 'k';
    protected $vColumn = 'v';

    /**
     * @var integer
     */
    const ACTION_SELECT = 1;
    const ACTION_INSERT = 2;
    const ACTION_UPDATE = 3;
    const ACTION_DELETE = 4;

    /**
     * @var mixed
     */
    protected $phppdo = null;

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->phppdo = $this->retrieveResource('pdo', 'globals');

        $this->object = new Object;
        $this->where  = new Object;
        $this->oper   = (new Object)->import([
            'i' => [],
            'u' => [],
            'd' => [],
        ]);
    }

    /**
     *
     * @param  mixed $c
     * @param  mixed $v
     * @return void
     */
    public function __set($c, $v)
    {
        if (empty($v)) {
            unset($this->$c);
            return;
        }

        if (null === ($o = $this->object->get($c))) {
            $this->object->set($c, array(
                'object' => $v,
                'action' => self::ACTION_INSERT,
            ));
            $this->oper->i->set($c, true);
            return;
        }
        
        if ($o->object === $v) {
            return;
        }

        if ($o->action == self::ACTION_SELECT || $o->action == self::ACTION_DELETE) {
            if ($o->action == self::ACTION_DELETE) {
                unset($this->oper->d->$c);
            }
            $o->action = self::ACTION_UPDATE;
            $this->oper->u->set($c, true);
        }

        $o->object = $v;
    }

    /**
     *
     * @param  mixed $c
     * @return mixed
     */
    public function __get($c)
    {
        if (null === ($o = $this->object->get($c)) || $o->action == self::ACTION_DELETE) {
            return;
        }

        return $o->object;
    }

    /**
     *
     * @param  mixed $c
     * @return boolean
     */
    public function __isset($c)
    {
        if (null === ($o = $this->object->get($c)) || $o->action == self::ACTION_DELETE) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param  mixed $c
     * @return void
     */
    public function __unset($c)
    {
        if (null === ($o = $this->object->get($c)) || $o->action == self::ACTION_DELETE) {
            return;
        } 
        
        if ($o->action == self::ACTION_SELECT) {
            $o->action = self::ACTION_DELETE;
            $this->oper->d->set($c, true);
        } else if ($o->action == self::ACTION_UPDATE) {
            $o->action = self::ACTION_DELETE;
            $this->oper->d->set($c, true);
            unset($this->oper->u->$c);
        } else {
            unset($this->object->$c);
            unset($this->oper->i->$c);
        }
    }

    /**
     *
     * @return void
     */
    public function find()
    {
        $sql = sprintf('SELECT `%s`, `%s` FROM %s', $this->kColumn, $this->vColumn, $this->schema());
        if ($this->where->count()) {
            $sql .= sprintf(' WHERE %s', $this->where->keys()->map(function($c) {
                return "`$c` = ?";
            })->join(' AND '));
        }

        $bind = $this->where->export(true);
        if (null === ($stmt = static::fetchStatement($sql, $bind))) {
            $stmt = $this->phppdo->prepare($sql);
            $stmt->execute($bind);
        }

        while (($o = $stmt->fetchObject())) {
            $this->object->set($o->{$this->kColumn}, array(
                'object' => $o->{$this->vColumn},
                'action' => self::ACTION_SELECT,
            ));

            if ($stmt instanceof PdoStatement) {
                static::cacheObject($o, $sql, $bind);
            }
        }
    }

    /**
     *
     * @return void
     */
    public function save()
    {
        foreach ($this->oper->i as $k => $ig) {
            $o = $this->object->$k;
            $binds = (new Object)->import($this->where->export());
            $this->phppdo->insert($this->schema(), $binds->import([
                $this->kColumn => $k,
                $this->vColumn => $o->object,
            ]));
            $o->action = self::ACTION_SELECT;
        }
        $this->oper->i = [];

        foreach ($this->oper->u as $k => $ig) {
            $o = $this->object->$k;
            $where = (new Object)->import($this->where->export());
            $this->phppdo->update($this->schema(), (new Object)->import([
                $this->vColumn => $o->object,
            ]), $where->import([
                $this->kColumn => $k,    
            ]));
            $o->action = self::ACTION_SELECT;
        }
        $this->oper->u = [];

        foreach ($this->oper->d as $k => $ig) {
            $o = $this->object->$k;
            $where = (new Object)->import($this->where->export());
            $this->phppdo->delete($this->schema(), $where->import([
                $this->kColumn => $k,
            ]));
            unset($this->object->$k);
        }
        $this->oper->d = [];
    }

    /**
     *
     * @return void
     */
    public function drop()
    {
        $this->phppdo->delete($this->schema(), $this->where);
    }

    /**
     *
     * @return integer
     */
    public function count()
    {
        return $this->object->count();
    }

    /**
     *
     * @return Traversable
     */
    public function getIterator()
    {
        return $this->object->map(function($o) {
            return $o->object;
        });
    }

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
}
