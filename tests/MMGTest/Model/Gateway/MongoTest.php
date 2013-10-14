<?php
/**
 * Tests for the Mongo Gateway.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
namespace MMGTest\Model\Gateway;

/**
 * @todo    Setup autoloading and remove this include.
 */
require_once dirname(__FILE__) . '/../../../../library/MMG/Model/Gateway/Mongo.php';

use PHPUnit_Framework_TestCase as TestCase;
use MMG\Model\Gateway\Mongo;
use MongoDB, MongoId;
use ReflectionClass;

/**
 * Tests for the Mongo Gateway.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class MongoTest extends TestCase
{
    
    /**
     * @var Mongo
     */
    public $gateway;
    
    /**
     * @var array
     */
    public $options = array(
		'driverClass'   => '\\MMGTest\\Model\\Gateway\\MongoClientMock',
        'user'          => 'testuser',
        'pass'          => 'testpass',
		'host'			=> 'localhost',
		'port'			=> 27017,
		'name'          => 'testdb',
        'driverOptions' => array(
        	'connect' => false,
        ),
    );

	/**
     * MongoDB mock
     *
     * @var MongoDB
     */
    public $mongoDb;

    /**
     * Set up the test
     */
    public function setUp ()
    {
        $this->gateway = $this->getMockBuilder('MMG\\Model\\Gateway\\Mongo')
            ->setMethods(array('_setMongoId'))
            ->setConstructorArgs(array($this->options))
            ->getMock();
        
        $this->mongoDb = $this->getMockBuilder('MongoDB')
            ->disableOriginalConstructor()
            ->getMock();
        
        $reflection = new ReflectionClass($this->gateway);
        $property = $reflection->getProperty('_driver');
        $property->setAccessible(true);
        $property->setValue($this->gateway, $this->mongoDb);
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
        $collectionName = 'books';
        $data = array('title' => 'The Fountainhead', 'stars' => 5);
        $mongoId = new MongoId;
        $idData = array('_id' => $mongoId) + $data;
        
        $collection = $this->_getMockCollection();
        
        $collection->expects($this->once())
			->method('insert')
			->with($this->identicalTo($idData));
        
        $this->mongoDb->expects($this->once())
			->method('__get')
			->with($this->equalTo($collectionName))
			->will($this->returnValue($collection));
        
        $this->gateway->expects($this->once())
            ->method('_setMongoId')
            ->with($this->identicalTo($data), $this->equalTo(true))
            ->will($this->returnValue($idData));
        
        $result = $this->gateway->create($collectionName, $data);
        
        $this->assertEquals($mongoId->__toString(), $result);
        
    } // END function testCreate
    
    /**
     * Test reading records from Mongo
     *
     * @return  void
     */
    public function testRead()
    {
        $collectionName = 'books';
        $criteria = array('stars' => 5);
        $expected = array(
            array('id' => new MongoId, 'title' => 'The Fountainhead', 'stars' => 5),
            array('id' => new MongoId, 'title' => 'Atlas Shrugged', 'stars' => 5),
        );
        
        $collection = $this->_getMockCollection();
        
        $collection->expects($this->once())
			->method('find')
			->with($this->identicalTo($criteria))
			->will($this->returnValue(new \ArrayIterator($expected)));
        
        $this->gateway->expects($this->once())
            ->method('_setMongoId')
            ->with($this->identicalTo($criteria), $this->equalTo(false))
            ->will($this->returnValue($criteria));
        
        $this->mongoDb->expects($this->once())
			->method('__get')
			->with($this->equalTo($collectionName))
			->will($this->returnValue($collection));
        
        $result = $this->gateway->read($collectionName, $criteria);
        
        $this->assertEquals($expected, $result);
        
    } // END function testRead
    
    /**
     * Test updating records.
     *
     * @return  void
     */
    public function testUpdate()
    {
        $collectionName = 'books';
        $data = array('title' => 'The Fountainhead', 'stars' => 5);
        $mongoId = new MongoId;
        $criteria = array('_id' => $mongoId->__toString());
        $expected = 1;
        $lastError = array('err' => null, 'n' => $expected, 'ok' => 1);
        
        $collection = $this->_getMockCollection();
        
        $collection->expects($this->once())
			->method('update')
			->with($this->identicalTo($criteria), $this->identicalTo($data));
		
		$this->gateway->expects($this->at(0))
            ->method('_setMongoId')
            ->with($this->identicalTo($data), $this->equalTo(false))
            ->will($this->returnValue($data));
        
        $this->gateway->expects($this->at(1))
            ->method('_setMongoId')
            ->with($this->identicalTo($criteria), $this->equalTo(false))
            ->will($this->returnValue($criteria));
        
        $this->mongoDb->expects($this->once())
			->method('__get')
			->with($this->equalTo($collectionName))
			->will($this->returnValue($collection));
        
        $this->mongoDb->expects($this->any())
                  ->method('lastError')
                  ->will($this->returnValue($lastError));
        
        $result = $this->gateway->update($collectionName, $data, $criteria);
        
        $this->assertEquals($expected, $result);
        
    } // END function testCreate
    
    /**
     * Test deleting records.
     *
     * @return  void
     */
    public function testDelete()
    {
        $collectionName = 'books';
        $criteria = array('stars' => 5);
        $expected = 2;
        $lastError = array('err' => null, 'n' => $expected, 'ok' => 1);
        
        $collection = $this->_getMockCollection();
        
        $collection->expects($this->once())
			->method('remove')
			->with($this->identicalTo($criteria));
		
		$this->gateway->expects($this->once())
            ->method('_setMongoId')
            ->with($this->identicalTo($criteria), $this->equalTo(false))
            ->will($this->returnValue($criteria));
        
        $this->mongoDb->expects($this->once())
			->method('__get')
			->with($this->equalTo($collectionName))
			->will($this->returnValue($collection));
        
        $this->mongoDb->expects($this->any())
                  ->method('lastError')
                  ->will($this->returnValue($lastError));
        
        $result = $this->gateway->delete($collectionName, $criteria);
        
        $this->assertEquals($expected, $result);
        
    } // END function testDelete
    
    /**
     * Provides data for testing _setMongoId().
     *
     * @return  array
     */
    public function provide_setMongoId()
    {
        return array(
            array(
                true,
                array('title' => 'The Fountainhead', 'stars' => 5),
                true,
            ),
            array(
                false,
                array('title' => 'The Fountainhead', 'stars' => 5),
                false,
            ),
            array(
                true,
                array(
                    '_id' => '525c07702cbc958e30a28c2f', 
                    'title' => 'The Fountainhead', 
                    'stars' => 5
                ),
                false,
            ),
            array(
                true,
                array(
                    '_id' => '525c07702cbc958e30a28c2f', 
                    'title' => 'The Fountainhead', 
                    'stars' => 5
                ),
                true,
            ),
        );
        
    } // END function provide_setMongoId
    
    /**
     * Test setting MongoId in data array.
     *
     * @param   boolean $expected Expected set status of the '_id' property
     * @param   array $data The data in which to set the MongoId
     * @param   boolean $force Whether or not to force setting a MongoId
     * @return  void
     * @dataProvider    provide_setMongoId
     */
    public function test_setMongoId($expected, array $data, $force)
    {
        $gateway = new Mongo($this->options);
        $reflection = new ReflectionClass($gateway);
        $method = $reflection->getMethod('_setMongoId');
        $method->setAccessible(true);
        $result = $method->invoke($gateway, $data, $force);
        
        if ($expected) {
            $this->assertTrue(isset($result['_id']));
            $this->assertInstanceOf('MongoId', $result['_id']);
            unset($data['_id'], $result['_id']);
        }
        
        // Check that all original data fieds were returned.
        $this->assertEquals($data, $result);
        
    } // END function test_setMongoId
    
    /**
     * Provides data for testing required options
     *
     * @return  array
     */
    public function provideRequireOption()
    {
        $class = '\MMG\Model\Gateway\Exception';
        
        return array(
            array(
                new $class("A 'name' option is required for this gateway"),
                'name',
            ),
            array(
                new $class("A 'host' option is required for this gateway"),
                'host',
            ),
        );
    } // END function provideRequireOption
    
    /**
     * Test that specific driver options are required.
     *
     * @param   \MMG\Model\Gateway\Exception $expected The Exception that should be thrown
     * @param   string $option The option to ommit
     * @return  void
     * @dataProvider    provideRequireOption
     */
    public function testRequiredOption(\MMG\Model\Gateway\Exception $expected, $option)
    {
        $options = $this->options;
        unset($options[$option]);
        
        $this->setExpectedException(
            get_class($expected),
            $expected->getMessage()
        );
        
        $gateway = new Mongo($options);
        
    } // END function testRequiredOptions
    
    /**
     * Returns a mock MongoCollection
     *
     * @return  MongoCollection
     */
    protected function _getMockCollection()
    {
        $collection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock();
        
        return $collection;
        
    } // END function _getMockCollection

} // END class MongoTest

class MongoClientMock extends \MongoClient
{
    
    /**
     * Returns instance of stdClass
     *
     * @return  \stdClass
     */
    public function __get($name)
    {
        return new \stdClass;
    } // END function __get
    
} // END class PDOMock
