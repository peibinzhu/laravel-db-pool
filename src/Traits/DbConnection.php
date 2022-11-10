<?php

declare(strict_types=1);

namespace PeibinLaravel\DbPool\Traits;

use Closure;

/**
 * TODO: release connection except transaction.
 */
trait DbConnection
{
    public function table($table, $as = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function raw($value)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function insert($query, $bindings = [])
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function update($query, $bindings = [])
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function delete($query, $bindings = [])
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function statement($query, $bindings = [])
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function affectingStatement($query, $bindings = [])
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function unprepared($query)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function prepareBindings(array $bindings)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function transaction(Closure $callback, $attempts = 1)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function transactionLevel()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function pretend(Closure $callback)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getDatabaseName()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function beginTransaction(): void
    {
        $this->setTransaction(true);
        $this->__call(__FUNCTION__, func_get_args());
    }

    public function commit(): void
    {
        $this->setTransaction(false);
        $this->__call(__FUNCTION__, func_get_args());
    }

    public function rollBack(): void
    {
        $this->setTransaction(false);
        $this->__call(__FUNCTION__, func_get_args());
    }
}
