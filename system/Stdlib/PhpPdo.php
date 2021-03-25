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
use Pdo;

/**
 *
 */
class PhpPdo extends Pdo
{
    /**
     *
     * @param  string $schema
     * @param  Object $bind
     * @return integer
     */
    public function insert($schema, Object $bind)
    {
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $schema, $bind->keys()->map(function($c) {
            return "`$c`";
        })->join(','), $bind->map(function($v) {
            return "?";
        })->join(','));

        $stmt = $this->prepare($sql);
        $stmt->execute($bind->export(true));

        return $stmt->rowCount();
    }

    /**
     *
     * @param  string      $schema
     * @param  Object      $bind
     * @param  Object|null $where
     * @return integer
     */
    public function update($schema, Object $bind, Object $where = null)
    {
        $sql = sprintf('UPDATE %s SET %s', $schema, $bind->keys()->map(function($c) {
            return "`$c` = ?";
        })->join(','));

        if ($where) {
            $sql .= sprintf(' WHERE %s', $where->keys()->map(function($c) {
                return "`$c` = ?";
            })->join(' AND '));
        }

        $stmt = $this->prepare($sql);
        $stmt->execute($where ? array_merge($bind->export(true), $where->export(true)) : $bind->export(true));

        return $stmt->rowCount();
    }

    /**
     *
     * @param  string      $schema
     * @param  Object|null $where
     * @return integer
     */
    public function delete($schema, Object $where = null)
    {
        $sql = sprintf('DELETE FROM %s', $schema);

        if ($where) {
            $sql .= sprintf(' WHERE %s', $where->keys()->map(function($c) {
                return "`$c` = ?";
            })->join(' AND '));
        }

        $stmt = $this->prepare($sql);
        $stmt->execute($where ? $where->export(true) : []);

        return $stmt->rowCount();
    }
}
