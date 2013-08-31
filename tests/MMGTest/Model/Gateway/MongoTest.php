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
		'driverClass'   => '\\stdClass',
        'username'      => 'testuser',
        'password'      => 'testpass',
		'host'			=> 'localhost',
		'port'			=> 27017,
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
        $this->gateway = new Mongo($this->options);
        
        $this->mongoClient = $this->getMock('MongoDB');
        
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
        $collection = 'books';
        $data = array('title' => 'The Fountainhead', 'stars' => 5);
        $id = '50b6afe544415ed606000000';
		$return = array_merge($data, array('_id' => new MongoId($id)));
        
        $this->mongoDb->expects($this->once())
			->method('save')
			->with($this->identicalTo($data))
			->will($this->returnValue($return));
        
        $result = $this->gateway->create($collection, $data);
        
        $this->assertEquals($id, $result);
        
    } // END function testCreate

} // END class MongoTest
