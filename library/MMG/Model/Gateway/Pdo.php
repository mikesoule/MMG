<?php
/**
 * PDO Gateway class.
 * 
 * Provides a standard gateway interface to PDO data sources.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
namespace MMG\Model\Gateway;

/**
 * Require the gateway abstract and interface.
 */
require_once dirname(__FILE__) . '/GatewayAbstract.php';
require_once dirname(__FILE__) . '/GatewayInterface.php';

use MMG\Model\Gateway\GatewayInterface;

/**
 * PDO Gateway class.
 * 
 * Provides a standard gateway interface to PDO data sources.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class Pdo extends GatewayAbstract implements GatewayInterface
{
    
    /**
     * Name of driver class to use.
     *
     * @var string
     */
    protected $_driverClass = '\PDO';
    
    /**
     * List of required driver options.
     *
     * @var array
     */
    protected $_requiredOptions = array('host', 'name');
    
    /**
     * Store a new record.
     *
     * @param   string $table The table or function for storing data
     * @param   array $data The data to be stored
     * @return  interger|string Unique identifier
     * @todo    Implement calling database functions
     */
    public function create($table, array $data)
    {
        $sql = $this->_getInsertSql($table, $data);
        $stmt = $this->_driver->query($sql);
        
        return $this->_driver->lastInsertId();
        
    } // END function create
    
    /**
     * Read data from storage.
     *
     * @param   string $table The path/table/collection to read from
     * @param   array $criteria Criteria for updating
     * @param   boolean $isFunction Flag for calling database functions
     * @return  array Multi-dimensional array of data read from storage
     */
    public function read($table, $criteria = array())
    {
        $sql = $this->_getSelectSql($table, $criteria);
        $stmt = $this->_driver->query($sql);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
    } // END function read
    
    /**
     * Update data in storage.
     *
     * @param   string $table The path/table/collection for the data
     * @param   array $data The data to be stored
     * @param   array $criteria Criteria for updating
     * @param   boolean $isFunction Flag for calling database functions
     * @return  integer The number of items updated
     */
    public function update($table, array $data, $criteria = array())
    {
        $sql = $this->_getUpdateSql($table, $data, $criteria);
        
        return $this->_driver->exec($sql);
        
    } // END function update
    
    /**
     * Delete data from storage.
     *
     * @param   string $table The path/table/collection to delete from
     * @param   array $criteria Criteria for deletion
     * @param   boolean $isFunction Flag for calling database functions
     * @return  integer The number of items updated
     */
    public function delete($table, $criteria = array())
    {
        $sql = $this->_getDeleteSql($table, $criteria);
        
        return $this->_driver->exec($sql);
        
    } // END function delete
    
    /**
     * Quote all values in an array to prevent SQL injection.
     *
     * @param   array $values Values to be quoted.
     * @return  array An array of quoted values
     */
    public function quoteValues(array $values)
    {
        foreach ($values as $key => $val) {
            $values[$key] = $this->_driver->quote($val);
        }
        
        return $values;
    } // END function quoteValues
    
    /**
     * Get a driver-specific SQL insert string.
     *
     * Note: This method assumes the first parameter in the data array
     * is the identity column.
     * 
     * @param   string $table The table name
     * @param   array $data The data to be inserted
     * @return  string
     */
    protected function _getInsertSql($table, array $data)
    {
        $columns    = implode(', ', array_keys($data));
        $values     = implode(', ', $this->quoteValues($data));
        $sql        = "INSERT INTO $table ($columns) VALUES ($values)";
        
        return $sql;
    } // END function _getInsertSql
    
    /**
     * Generate an SQL SELECT string.
     *
     * @param   string $table The table name
     * @param   array $criteria Criteria for selecting
     * @return  string
     */
    protected function _getSelectSql($table, array $criteria)
    {
        $sql = "SELECT * FROM $table";
        
        if (!empty($criteria)) {
            $where = $this->_getWhere($criteria);
            $sql .= " WHERE $where";
        }
        
        return $sql;
    } // END function _getSelectSql
    
    /**
     * Generate an SQL update string.
     *
     * @param   string $table The table name
     * @param   array $data The data to be inserted
     * @param   array $criteria Criteria for updating
     * @return  string
     */
    protected function _getUpdateSql($table, array $data, array $criteria)
    {
        $data = $this->quoteValues($data);
        $columns = array();
        
        foreach ($data as $key => $val) {
            $columns[] = "$key = $val";
        }
        
        $values = implode(', ', $columns);
        $where = $this->_getWhere($criteria);
        
        return "UPDATE $table SET $values WHERE $where";
    } // END function _getUpdateSql
    
    /**
     * Generate an SQL DELETE string.
     *
     * @param   string $table The table name
     * @param   array $criteria Criteria for deletion
     * @return  string
     */
    protected function _getDeleteSql($table, array $criteria)
    {
        $sql = "DELETE FROM $table";
        
        if (!empty($criteria)) {
            $where = $this->_getWhere($criteria);
            $sql .= " WHERE $where";
        }
        
        return $sql;
    } // END function _getDeleteSql
    
    /**
     * Generate an SQL WHERE clause from a criteria array.
     *
     * @param   array $criteria column => value array
     * @return  string
     */
    protected function _getWhere(array $criteria)
    {
        $criteria = $this->quoteValues($criteria);
        $values = array();
        
        foreach ($criteria as $key => $val) {
            $values[] = "$key = $val";
        }
        
        return implode(' AND ', $values);
    } // END function _getWhere
    
    /**
     * Initialize the driver instance.
     *
     * @param   array $options
     * @return  void
     */
    protected function _initDriver(array $options)
    {
        $dsn = $this->_getDsn($options);
        $driverOptions = array();
        
        if (isset($options['driverOptions'])) {
            $driverOptions = $options['driverOptions'];
        }
        
        $user = isset($options['user']) ? $options['user'] : null;
        $pass = isset($options['pass']) ? $options['pass'] : null;
        $class = $this->_driverClass;
        
        $this->_driver = new $class($dsn, $user, $pass, $driverOptions);
        
    } // END function _initDriver
    
    /**
     * Return a DSN string from array of options.
     *
     * @param   array $options
     * @return  string
     */
    protected function _getDsn(array $options)
    {
        $dsn = "mysql:host={$options['host']};";
        
        if (isset($options['port'])) {
            $dsn .= "port={$options['port']};";
        }
        
        $dsn .= "dbname={$options['name']}";
        
        return $dsn;
        
    } // END function _getDsn
    
} // END class Pdo
