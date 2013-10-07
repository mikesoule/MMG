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
     * Constructor
     *
     * @param   array $options
     * @return  void
     */
    public function __construct($options = array())
    {    
        if (array_key_exists('driverClass', $options)) {
            $this->_driverClass = $options['driverClass'];
        }
        
        $dsn            = isset($options['dsn']) ? $options['dsn'] : null;
        $username       = isset($options['username']) ? $options['username'] : null;
        $password       = isset($options['password']) ? $options['password'] : null;
        $driverOptions  = isset($options['driverOptions']) ? $options['driverOptions'] : array();
        
        $this->_initPdo($dsn, $username, $password, $driverOptions);
    } // END function __construct
    
    /**
     * Store a new record.
     *
     * @param   string $store The table or function for storing data
     * @param   array $data The data to be stored
     * @param   string $sequence The sequence name for a primary/increment key
     * @param   boolean $isFunction Flag for calling database functions
     * @return  interger|string Unique identifier
     * @todo    Implement calling database functions
     */
    public function create($store, array $data, $sequence = null, $isFunction = false)
    {
        return $this->_insert($store, $data, $sequence);
    } // END function create
    
    /**
     * Read data from storage.
     *
     * @param   string $store The path/table/collection to read from
     * @param   array $criteria Criteria for updating
     * @param   boolean $isFunction Flag for calling database functions
     * @return  array Multi-dimensional array of data read from storage
     */
    public function read($store, $criteria = array(), $isFunction = false)
    {
        return $this->_read($store, $criteria);
    } // END function read
    
    /**
     * Update data in storage.
     *
     * @param   string $store The path/table/collection for the data
     * @param   array $data The data to be stored
     * @param   array $criteria Criteria for updating
     * @param   boolean $isFunction Flag for calling database functions
     * @return  integer The number of items updated
     */
    public function update($store, array $data, $criteria = array(), $isFunction = false)
    {
        return $this->_update($store, $data, $criteria);
    } // END function update
    
    /**
     * Delete data from storage.
     *
     * @param   string $store The path/table/collection to delete from
     * @param   array $criteria Criteria for deletion
     * @param   boolean $isFunction Flag for calling database functions
     * @return  integer The number of items updated
     */
    public function delete($store, $criteria = array(), $isFunction = false)
    {
        return $this->_delete($store, $criteria);
    } // END function delete
    
    /**
     * Insert data into a table and return the last insert ID.
     *
     * @param   string $table The table name
     * @param   array $data The data to be inserted
     * @param   string $sequence The sequence name for a primary/increment key
     * @return  integer|string
     */
    protected function _insert($table, array $data, $sequence = null)
    {
        $sql = $this->_getInsertSql($table, $data);
        $stmt = $this->_driver->query($sql);
        
        return $this->_driver->lastInsertId($sequence);
    } // END function _insert
    
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
        $values     = implode(', ', $this->_quoteValues($data));
        $sql        = "INSERT INTO $table ($columns) VALUES ($values)";
        
        return $sql;
    } // END function _getInsertSql
    
    /**
     * Return rows from a database.
     *
     * @param   string $table The table name
     * @param   array $criteria Criteria for selecting
     * @return  array
     */
    protected function _read($table, array $criteria)
    {
        $sql = $this->_getSelectSql($table, $criteria);
        $stmt = $this->_driver->query($sql);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } // END function _read
    
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
     * Update data in a table and return the number of affected rows.
     *
     * @param   string $table The table name
     * @param   array $data The data to be inserted
     * @param   array $criteria Criteria for updating
     * @return  integer The number of rows affected by the update
     */
    protected function _update($table, array $data, array $criteria)
    {
        $sql = $this->_getUpdateSql($table, $data, $criteria);
        
        return $this->_driver->exec($sql);
    } // END function _update
    
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
        $data = $this->_quoteValues($data);
        $columns = array();
        
        foreach ($data as $key => $val) {
            $columns[] = "$key = $val";
        }
        
        $values = implode(', ', $columns);
        $where = $this->_getWhere($criteria);
        
        return "UPDATE $table SET $values WHERE $where";
    } // END function _getUpdateSql
    
    /**
     * Delete records from a table.
     *
     * @return  void
     */
    protected function _delete($table, array $criteria)
    {
        $sql = $this->_getDeleteSql($table, $criteria);
        
        return $this->_driver->exec($sql);
    } // END function _delete
    
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
        $criteria = $this->_quoteValues($criteria);
        $values = array();
        
        foreach ($criteria as $key => $val) {
            $values[] = "$key = $val";
        }
        
        return implode(' AND ', $values);
    } // END function _getWhere
    
    /**
     * Quote all values in an array to prevent SQL injection.
     *
     * @param   array $values Values to be quoted.
     * @return  array An array of quoted values
     */
    protected function _quoteValues(array $values)
    {
        foreach ($values as $key => $val) {
            $values[$key] = $this->_driver->quote($val);
        }
        
        return $values;
    } // END function _quoteValues
    
    /**
     * Initialize the PDO instance.
     *
     * @param   string $dsn
     * @param   string $username
     * @param   string $password
     * @param   array $driverOptions
     * @return  void
     */
    protected function _initPdo($dsn, $username = null, $password = null, array $driverOptions = array())
    {
        $class = $this->_driverClass;
        $this->_driver = new $class($dsn, $username, $password, $driverOptions);
    } // END function _initPdo
    
} // END class Pdo
