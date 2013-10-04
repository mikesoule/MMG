<?php
/**
 * Select class.
 * 
 * Provides an OOP interface to SQL SELECT statement generation.
 *
 * @category    MMG
 * @package     MMG/Model/Gateway
 * @subpackage  MMG/Model/Gateway/Pdo
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
namespace MMG\Model\Gateway\Pdo;

/**
 * Select class.
 * 
 * Provides an OOP interface to SQL SELECT statement generation.
 *
 * @category    MMG
 * @package     MMG/Model/Gateway
 * @subpackage  MMG/Model/Gateway/Pdo
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class Select
{
    
    /**
     * SQL clauses for this statement.
     *
     * @var array
     */
    protected $_spec = array(
        'SELECT'    => array(),
        'FROM'      => null,
        'JOINS'     => array(),
        'WHERE'     => array(),
        'GROUP'     => array(),
        'HAVING'    => array(),
        'ORDER'     => array(),
        'LIMIT'     => null,
        'OFFSET'    => null,
    );

    /**
     * Constructor
     *
     * @param   array $columns
     * @param   string|null $table
     * @param   string|null $alias
     */
    public function __construct(array $columns = array('*'), $table = null, $alias = null)
    {
        $this->_spec['SELECT'] = $columns;
        
        if ($table) {
            $this->from($table, $alias);
        }
        
    } // END function __construct
    
    /**
     * Adds a FROM clause to the SQL statement.
     *
     * @param   string $table The table name
     * @param   string|null $alias An optional alias for the table
     * @return  Select Provides a fluent interface
     */
    public function from($table, $alias = null)
    {
        if ($alias) {
            $table = array($alias => $table);
        }
        
        $this->_spec['FROM'] = $table;
        
        return $this;
        
    } // END function from
    
    /**
     * Adds a JOIN clause.
     *
     * @param   string $type The join type
     * @param   string $table The table name
     * @param   string|null $alias An optional alias for the table
     * @param   string $fromCol The column to join against in the "FROM" clause
     * @param   string $joinCol The column to join on
     * @return  Select Provides a fluent interface
     */
    public function join($type = 'INNER JOIN', $table, $alias = null, $fromCol, $joinCol)
    {
        if (!is_null($alias)) {
            $table = array($alias => $table);
        }
        
        $this->_spec['JOIN'][] = array($type, $table, array($fromCol => $joinCol));
        
        return $this;
        
    } // END function join
    
    /**
     * Adds a WHERE clause.
     *
     * @param   string $column The column name
     * @param   string $operator The comparison operator
     * @param   mixed $value The value to match
     * @return  Select Provides a fluent interface
     */
    public function where($column, $operator, $value)
    {
        $this->_spec['WHERE'][] = array($column, $operator, $value);
        
        return $this;
        
    } // END function where
    
    /**
     * Adds an AND condition to the where clause.
     *
     * @param   string $column The column name
     * @param   string $operator The comparison operator
     * @param   mixed $value The value to match
     * @return  Select Provides a fluent interface
     */
    public function andWhere($column, $operator, $value)
    {
        $this->_requireClause('WHERE');
        
        $this->_spec['WHERE'][] = 'AND';
        
        return $this->where($column, $operator, $value);
        
    } // END function andWhere
    
    /**
     * Adds an OR condition to the where clause.
     *
     * @param   string $column The column name
     * @param   string $operator The comparison operator
     * @param   mixed $value The value to match
     * @return  Select Provides a fluent interface
     */
    public function orWhere($column, $operator, $value)
    {
        $this->_requireClause('WHERE');
        
        $this->_spec['WHERE'][] = 'OR';
        
        return $this->where($column, $operator, $value);
        
    } // END function orWhere
    
    /**
     * Adds a nested WHERE condition.
     *
     * @param   array $where
     * @return  Select Provides a fluent interface
     */
    public function andNestedWhere(array $where)
    {
        $this->_requireClause('WHERE');
        
        $this->_spec['WHERE'][] = 'AND';
        $this->_spec['WHERE'][] = array($where);
        
        return $this;
        
    } // END function andNestedWhere
    
    /**
     * Adds a nested WHERE condition.
     *
     * @param   array $where
     * @return  Select Provides a fluent interface
     */
    public function orNestedWhere(array $where)
    {
        $this->_requireClause('WHERE');
        
        $this->_spec['WHERE'][] = 'OR';
        $this->_spec['WHERE'][] = array($where);
        
        return $this;
        
    } // END function orNestedWhere
    
    /**
     * GROUP BY the specified columns.
     *
     * @param   array $columns
     * @return  Select Provides a fluent interface
     */
    public function group(array $columns)
    {
        $this->_spec['GROUP'] = $columns;
        
        return $this;
        
    } // END function group
    
    /**
     * Adds a HAVING clause.
     *
     * @param   string $expression A column or function with column argument
     * @param   string $operator The comparison operator
     * @param   mixed $value The value to match
     * @return  Select Provides a fluent interface
     */
    public function having($expression, $operator, $value)
    {
        $this->_requireClause('GROUP');
        
        $this->_spec['HAVING'][] = array($expression, $operator, $value);
        
        return $this;
        
    } // END function having
    
    /**
     * Adds a HAVING AND clause.
     *
     * @param   string $expression A column or function with column argument
     * @param   string $operator The comparison operator
     * @param   mixed $value The value to match
     * @return  Select Provides a fluent interface
     */
    public function andHaving($expression, $operator, $value)
    {
        $this->_requireClause('GROUP');
        $this->_requireClause('HAVING');
        
        $this->_spec['HAVING'][] = 'AND';
        
        return $this->having($expression, $operator, $value);
        
    } // END function andHaving
    
    /**
     * Adds a HAVING OR clause.
     *
     * @param   string $expression A column or function with column argument
     * @param   string $operator The comparison operator
     * @param   mixed $value The value to match
     * @return  Select Provides a fluent interface
     */
    public function orHaving($expression, $operator, $value)
    {
        $this->_requireClause('GROUP');
        $this->_requireClause('HAVING');
        
        $this->_spec['HAVING'][] = 'OR';
        
        return $this->having($expression, $operator, $value);
        
    } // END function orHaving
    
    /**
     * Return an SQL string.
     *
     * @return  string
     * @throws  MMG\Model\Exception
     */
    public function getSql()
    {
        $this->_requireValidSpec();
        
        $parts = array(
            $this->_getSqlSelect(),
            $this->_getSqlFrom(),
            $this->_getSqlJoin(),
            $this->_getSqlWhere(),
            $this->_getSqlGroup(),
            $this->_getSqlHaving(),
            $this->_getSqlOrder(),
        );
        
        // Remove nulls
        $parts = array_filter($parts);
        
        return implode(' ', $parts);
        
    } // END function getSql
    
    /**
     * Return the SELECT part of the SQL string.
     *
     * @return  string
     */
    protected function _getSqlSelect()
    {
        $callback = function ($column) {
            if (is_array($column)) {
                return current($column) . ' AS ' . key($column);
            }
            
            return $column;
        };
        
        $columns = array_map($callback, $this->_spec['SELECT']);
        
        return 'SELECT ' . implode(', ', $columns);
        
    } // END function _getSqlSelect
    
    /**
     * Return the FROM part of the SQL string.
     *
     * @return  string|null
     */
    protected function _getSqlFrom()
    {
        $from = $this->_spec['FROM'];
        
        if (is_array($from)) {
            $from = current($from) . ' AS ' . key($from);
        }
        
        if (empty($from)) {
            return null;
        }
        
        return "FROM $from";
        
    } // END function _getSqlFrom
    
    /**
     * Return the JOIN part of the SQL string.
     *
     * @return  string
     */
    protected function _getSqlJoin()
    {
        $joins = array();
        
        foreach ($this->_spec['JOINS'] as $join) {
            $type = array_shift($join);
            $table = array_shift($join);
            $cols = array_shift($join);
        
            if (is_array($table)) {
                $table = current($table) . ' ' . key($table);
            }
            
            $parts = array(
                $type,
                $table, 
                'ON', 
                key($cols), 
                '=', 
                current($cols),
            );
            
            $joins[] = implode(' ', $parts);
        }
        
        return implode(' ', $joins);
        
    } // END function _getSqlJoin
    
    /**
     * Return the WHERE part of the SQL string.
     *
     * @return  string
     */
    protected function _getSqlWhere()
    {
        $wheres = array();
        
        foreach ($this->_spec['WHERE'] as $where) {
            $wheres[] = $this->_getWhereCondition($where);
        }
        
        if (empty($wheres)) {
            return null;
        }
        
        return 'WHERE ' . implode(' ', $wheres);
        
    } // END function _getSqlWhere
    
    /**
     * Return the GROUP part of the SQL string.
     *
     * @return  string|null
     */
    protected function _getSqlGroup()
    {
        $group = $this->_spec['GROUP'];
        
        if (empty($group)) {
            return null;
        }
        
        return 'GROUP BY ' . implode(', ', $group);
        
    } // END function _getSqlGroup
    
    /**
     * Return the HAVING part of the SQL string.
     *
     * @return  string|null
     */
    protected function _getSqlHaving()
    {
        $callback = function ($having) {
            if (is_array($having)) {
                return implode(' ', $having);
            }
            
            return $having;
        };
        
        $havings = array_map($callback, $this->_spec['HAVING']);
        
        if (empty($havings)) {
            return null;
        }
        
        return empty($havings) ? null : 'HAVING ' . implode(' ', $havings);
        
    } // END function _getSqlHaving
    
    /**
     * Return the ORDER part of the SQL string.
     *
     * @return  string|null
     */
    protected function _getSqlOrder()
    {
        $order = $this->_spec['ORDER'];
        
        if (empty($order)) {
            return null;
        }
        
        return 'ORDER BY ' . implode(', ', $order);
        
    } // END function _getSqlOrder
    
    /**
     * Builds a WHERE condition string and returns it.
     *
     * @param   array $where
     * @return  string
     */
    protected function _getWhereCondition(array $where)
    {
        if (is_array($where[0])) {
            return '(' . $this->_getWhereCondition($where) . ')';
        }
        
        return implode(' ', $where);
        
    } // END function _getWhereCondition
    
    /**
     * Throws an exception if the specified clause does not exist.
     *
     * @param   string $clause
     * @return  void
     * @throws  Exception
     */
    protected function _requireClause($clause)
    {
        if (empty($this->_spec[$clause])) {
            throw new Exception("Must contain a '$clause' clause.");
        }
        
    } // END function _requireClause
    
    /**
     * Ensure the required SQL clauses are specified.
     *
     * @return  void
     * @throws  Exception
     */
    protected function _requireValidSpec()
    {
        $required = array('SELECT');
        
        foreach ($required as $clause) {
            $this->_requireClause($clause);
        }
        
    } // END function _requireValidSpec

} // END class Select
