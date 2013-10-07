<?php
/**
 * Tests for the PDO Gateway.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
namespace MMGTest\Model\Gateway;

/**
 * @todo    Setup autoloading and remove this include.
 */
require_once dirname(__FILE__) . '/../../../../library/MMG/Model/Gateway/Pdo.php';

use PHPUnit_Framework_TestCase as TestCase;
use MMG\Model\Gateway\Pdo;
use PDOStatement;
use ReflectionClass;

/**
 * Tests for the PDO Gateway.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class PdoTest extends TestCase
{
    
    /**
     * @var Pdo
     */
    public $gateway;
    
    /**
     * @var array
     */
    public $options = array(
        'driverClass'      => '\\MMGTest\\Model\\Gateway\\PDOMock',
        'dsn'           => 'mysql:dbname=testdb;host=localhost',
        'username'      => 'testuser',
        'password'      => 'testpass',
        'driverOptions' => array(),
    );
    
    /**
     * PDO mock
     *
     * @var PDO
     */
    public $pdo;

    /**
     * Set up the test
     */
    public function setUp ()
    {
        $this->gateway = new Pdo($this->options);
        
        $this->pdo = $this->getMockBuilder('MMGTest\\Model\\Gateway\\PDOMock')
            ->setMethods(array('query', 'exec', 'getAttribute', 'lastInsertId'))
            ->getMock();
        
        $reflection = new ReflectionClass($this->gateway);
        $property = $reflection->getProperty('_driver');
        $property->setAccessible(true);
        $property->setValue($this->gateway, $this->pdo);
    }

    /**
     * Tear down the test
     */
    public function tearDown ()
    {}
    
    /**
     * Ensure that the gateway interface is implemented.
     *
     * @return  void
     */
    public function testImplementsGatewayInterface()
    {
        $this->assertInstanceOf(
            'MMG\\Model\\Gateway\\GatewayInterface', 
            $this->gateway
        );
    } // END function testImplementsGatewayInterface
    
    /**
     * Test creating new records.
     *
     * @return  void
     */
    public function testCreate()
    {
        $table = 'books';
        $data = array('title' => 'The Fountainhead', 'stars' => 5);
        $insertId = 1234;
        $sql = "INSERT INTO books (title, stars) VALUES ('The Fountainhead', '5')";
        
        $this->pdo->expects($this->once())
                  ->method('query')
                  ->with($this->identicalTo($sql))
                  ->will($this->returnValue($this->_getMockPDOStatement(
                        array()
                  )));
        
        $this->pdo->expects($this->once())
                  ->method('lastInsertId')
                  ->with($this->isNull())
                  ->will($this->returnValue($insertId));
        
        $result = $this->gateway->create($table, $data);
        
        $this->assertEquals($insertId, $result);
        
    } // END function testCreate
    
    /**
     * Test creating new records with a sequence name for 
     * obtaining the insert ID.
     *
     * @return  void
     */
    public function testCreateWithSequenceName()
    {
        $table = 'books';
        $data = array('title' => 'The Fountainhead', 'stars' => 5);
        $sequence = 'books_id_seq';
        $insertId = 1234;
        $sql = "INSERT INTO books (title, stars) VALUES ('The Fountainhead', '5')";
        
        $this->pdo->expects($this->once())
                  ->method('query')
                  ->with($this->identicalTo($sql))
                  ->will($this->returnValue($this->_getMockPDOStatement(
                        array()
                  )));
        
        $this->pdo->expects($this->once())
                  ->method('lastInsertId')
                  ->with($this->identicalTo($sequence))
                  ->will($this->returnValue($insertId));
        
        $result = $this->gateway->create($table, $data, $sequence);
        
        $this->assertEquals($insertId, $result);
        
    } // END function testCreateWithSequenceName
    
    /**
     * Test selecting records from a database.
     *
     * @return  void
     */
    public function testRead()
    {
        $table = 'books';
        $criteria = array('stars' => 5);
        $sql1  = "SELECT * FROM books WHERE stars = '5'";
        $sql2  = "SELECT * FROM books";
        $expected = array(
            array('id' => 1234, 'title' => 'The Fountainhead', 'stars' => 5),
            array('id' => 5678, 'title' => 'Atlas Shrugged', 'stars' => 5),
        );
        
        $this->pdo->expects($this->at(0))
                  ->method('query')
                  ->with($this->identicalTo($sql1))
                  ->will($this->returnValue(
                        $this->_getMockPDOStatement($expected)
                  ));
        
        $this->pdo->expects($this->at(1))
                  ->method('query')
                  ->with($this->identicalTo($sql2))
                  ->will($this->returnValue(
                        $this->_getMockPDOStatement($expected)
                  ));
        
        $result1 = $this->gateway->read($table, $criteria);
        $result2 = $this->gateway->read($table);
        
        $this->assertEquals($expected, $result1);
        $this->assertEquals($expected, $result2);
    } // END function testRead
    
    /**
     * Test updating records.
     *
     * @return  void
     */
    public function testUpdate()
    {
        $table = 'books';
        $data = array('title' => 'The Fountainhead', 'stars' => '5');
        $criteria = array('id' => 1234);
        $sql  = "UPDATE books ";
        $sql .= "SET title = 'The Fountainhead', stars = '5' ";
        $sql .= "WHERE id = '1234'";
        $expected = 1;
        
        $this->pdo->expects($this->any())
                  ->method('exec')
                  ->with($this->identicalTo($sql))
                  ->will($this->returnValue($expected));
        
        $result = $this->gateway->update($table, $data, $criteria);
        
        $this->assertEquals($expected, $result);
        
    } // END function testCreate
    
    /**
     * Test deleting records.
     *
     * @return  void
     */
    public function testDelete()
    {
        $table = 'books';
        $criteria = array('stars' => 5);
        $sql1  = "DELETE FROM books WHERE stars = '5'";
        $sql2  = "DELETE FROM books";
        $expected = 2;
        
        $this->pdo->expects($this->at(0))
                  ->method('exec')
                  ->with($this->identicalTo($sql1))
                  ->will($this->returnValue($expected));
        
        $this->pdo->expects($this->at(1))
                  ->method('exec')
                  ->with($this->identicalTo($sql2))
                  ->will($this->returnValue($expected));
        
        $result1 = $this->gateway->delete($table, $criteria);
        $result2 = $this->gateway->delete($table);
        
        $this->assertEquals($expected, $result1);
        $this->assertEquals($expected, $result2);
    } // END function testDelete
    
    /**
     * Sets the return value of PDO:getAttribute().
     *
     * @param   string $attr
     * @return  void
     */
    protected function _setGetAttributeReturn($attr)
    {
        $this->pdo->expects($this->any())
                  ->method('getAttribute')
                  ->will($this->returnValue($attr));
    } // END function _setPdoDriver
    
    /**
     * Returns a mock PDOStatement with preloaded results.
     *
     * @param   array $results
     * @return  PDOStatement Mock instance
     */
    protected function _getMockPDOStatement(array $results = array())
    {
        $identity = null;
        
        if (isset($results[0])) {
            $identity = current($results[0]);
        }
        
        $stmt = $this->getMockBuilder('PDOStatement')
                     ->setMethods(array('fetchColumn', 'fetchAll'))
                     ->getMock();
        
        $stmt->expects($this->any())
             ->method('fetchColumn')
             ->will($this->returnValue($identity));
        
        $stmt->expects($this->any())
             ->method('fetchAll')
             ->will($this->returnValue($results));
        
        return $stmt;
    } // END function _getMockPDOStatement
    
} // END class PdoTest

class PDOMock extends \PDO
{
    
    /**
     * Override the PDO constructor
     *
     * @return  void
     */
    public function __construct(){}
    
    /**
     * Mock quoting a value.
     *
     * @param   mixed $value
     * @param   integer $parameterType
     * @return  string
     */
    public function quote($value, $parameterType = null)
    {
        return "'$value'";
    } // END function quote
    
} // END class PDOMock
