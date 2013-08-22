<?php
/**
 * Mapper tests.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Mapper
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
namespace MMGTest\Model\Mapper;

/**
 * @todo    Setup autoloading and remove this include.
 */
require_once dirname(__FILE__) . '/../../../library/MMG/Model/Mapper.php';
require_once dirname(__FILE__) . '/../../../library/MMG/Model/Collection.php';
require_once dirname(__FILE__) . '/../../../library/MMG/Model.php';
require_once dirname(__FILE__) . '/../../../library/MMG/Model/Gateway/Pdo.php';
require_once dirname(__FILE__) . '/../../../library/MMG/Model/Mapper/DataMap.php';

use PHPUnit_Framework_TestCase as TestCase;
use MMG\Model\Model;
use MMG\Model\Collection;
use MMG\Model\Mapper\Mapper;
use MMG\Model\Mapper\DataMap;
use MMG\Model\Gateway\Pdo;
use ReflectionClass;
use DateTime;

/**
 * Abstract mapper tests.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Mapper
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class MapperTest extends TestCase
{
    
    /**
     * Gateway instance
     *
     * @var GatewayInterface
     */
    public $gateway;
    
    /**
     * The gateway name for testing
     *
     * @var string
     */
    public $gatewayName = 'mydb';
    
    /**
     * Suffix to ensure unique class names
     *
     * @var string
     */
    protected $_classSuffix;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->_classSuffix = uniqid();
    }

    /**
     * Tear down the test
     */
    public function tearDown()
    {}
    
    /**
     * Ensure the constructor is working
     *
     * @return	void
     */
    public function test__construct()
    {
        $options = array(
            'modelClass'    => 'Test_Model_Class',
            'idProperty'    => 'testId',
            'gateway'       => 'testGateway',
        );
        
        $mapper = $this->_getMockMapper();
        $mapper->__construct($options);
        
        $reflection = new ReflectionClass($mapper);
        
        // Assertions
        foreach ($options as $key => $val) {
            $this->assertAttributeEquals($val, "_$key", $mapper);
        }
    } // END function test__construct
    
    /**
     * Ensure that a model can be stored to a gateway.
     *
     * @return  void
     */
    public function testSave()
    {
        $model = $this->_getMockModel(array('id' => null, 'name' => 'one'));
        
        $mapper = $this->_getMockMapper();
        
        $identity = 123;
        
        $map1 = new DataMap;
        $map1->idProperty = 'id';
        $map1->gateway = $this->gatewayName;
        $map1->store = 'mytable';
        $map1->data = array(
            'entityId' => null,
            'entityName' => 'one',
        );
        
        $map2 = new DataMap;
        $map2->idProperty = 'id';
        $map2->gateway = $this->gatewayName;
        $map2->store = 'mytable';
        $map2->data = array(
            'entityId' => $identity,
            'entityName' => 'one',
        );
        
        $createData = array('id' => null, 'name' => 'one');
        $updateData = array('id' => $identity, 'name' => 'one');
        
        $mapper->expects($this->at(0))
            ->method('_mapToGateways')
            ->with($this->equalTo($createData))
            ->will($this->returnValue(array($map1)));
        
        $mapper->expects($this->at(1))
            ->method('_mapToGateways')
            ->with($this->equalTo($updateData))
            ->will($this->returnValue(array($map2)));
        
        $this->gateway->expects($this->once())
            ->method('create')
            ->will($this->returnValue($identity));
        
        $this->gateway->expects($this->once())
            ->method('update')
            ->will($this->returnValue(1));
        
        $mapper->save($model);
        
        $this->assertEquals($identity, $model->getIdentity());
        
        $mapper->save($model);
    } // END function testSave
    
    /**
     * Test deleting a model.
     *
     * @return  void
     */
    public function testDelete()
    {
        $modelData = array('id' => 123, 'name' => 'one');
        $model = $this->_getMockModel($modelData);
        
        $mapper = $this->_getMockMapper();
        
        $map = new DataMap;
        $map->gateway = $this->gatewayName;
        $map->store = 'mytable';
        $map->criteria = array('entityId' => 123);
        
        $mapper->expects($this->once())
            ->method('_mapToGateways')
            ->with($this->equalTo($modelData))
            ->will($this->returnValue(array($map)));
        
        $this->gateway->expects($this->once())
            ->method('delete')
            ->will($this->returnValue(1));
        
        $mapper->delete($model);
    } // END function testDelete
    
    /**
     * Test finding and returning model collections.
     *
     * @return  void
     */
    public function testFind()
    {
        $mockModel = $this->_getMockModel();
        $modelClass = get_class($mockModel);
        
        $mapper = $this->_getMockMapper(array(
            'modelClass' => $modelClass
        ));
        
        $modelData = array(
            array(
                'id'    => 1,
                'name'  => 'One',
                'type'  => 'Fish',
            ),
            array(
                'id'    => 2,
                'name'  => 'Two',
                'type'  => 'Fish',
            ),
        );
        
        $gatewayData = array(
            array(
                'entityId'    => 1,
                'entityName'  => 'One',
                'entityType'  => 'Fish',
            ),
            array(
                'entityId'    => 2,
                'entityName'  => 'Two',
                'entityType'  => 'Fish',
            ),
        );
        
        $criteria = array('type' => 'Fish');
        
        $gatewayMap = new DataMap;
        $gatewayMap->gateway = $this->gatewayName;
        $gatewayMap->store = 'mytable';
        $gatewayMap->data = array(
            'entityType' => $criteria['type'],
        );
        
        $modelMap1 = new DataMap;
        $modelMap1->data = $modelData[0];
        
        $modelMap2 = new DataMap;
        $modelMap2->data = $modelData[1];
        
        $mapper->expects($this->once())
            ->method('_mapToSearchGateway')
            ->with($this->equalTo($criteria))
            ->will($this->returnValue($gatewayMap));
        
        $mapper->expects($this->at(1))
            ->method('_mapToModel')
            ->with($this->equalTo($gatewayData[0]))
            ->will($this->returnValue($modelMap1));
        
        $mapper->expects($this->at(2))
            ->method('_mapToModel')
            ->with($this->equalTo($gatewayData[1]))
            ->will($this->returnValue($modelMap2));
        
        $this->gateway->expects($this->once())
            ->method('read')
            ->will($this->returnValue($gatewayData));
        
        $result = $mapper->find($criteria);
        
        $expected = new Collection(array(
            new $modelClass($modelData[0]),
            new $modelClass($modelData[1]),
        ));
        
        // For testing instance cache
        $expected2 = $result->current();
        $result2 = $mapper->find(array(
            'id' => $expected2->getIdentity()
        ))->current();
        
        $this->assertEquals($expected, $result);
        $this->assertEquals($expected2, $result2);
        
    } // END function testFind
    
    /**
     * Test finding a single model.
     *
     * @return  void
     */
    public function testFindOne()
    {
        $mockModel = $this->_getMockModel();
        $modelClass = get_class($mockModel);
        
        $mapper = $this->_getMockMapper(
            array('modelClass' => $modelClass),
            array(
                '_mapToSearchGateway', '_mapToGateways', '_mapToModel', 'find'
            )
        );
        
        $modelData = array(
            array(
                'id'    => 1,
                'name'  => 'One',
                'type'  => 'Fish',
            ),
            array(
                'id'    => 2,
                'name'  => 'Two',
                'type'  => 'Fish',
            ),
        );
        
        $model1 = new $modelClass($modelData[0]);
        $model2 = new $modelClass($modelData[1]);
        $collection = new Collection(array(
            $model1, $model2
        ));
        
        $mapper->expects($this->once())
            ->method('find')
            ->will($this->returnValue($collection));
        
        $result = $mapper->findOne(array('id' => 1));
        
        $this->assertSame($model1, $result);
        
    } // END function testFindOne
    
    /**
     * Test getting an existing and non-existing gateway.
     *
     * @return  void
     */
    public function test_getGateway()
    {
        $mapper = $this->_getMockMapper();
        
        $reflection = new ReflectionClass($mapper);
        $method = $reflection->getMethod('_getGateway');
        $method->setAccessible(true);
        $result = $method->invoke($mapper, $this->gatewayName);
        
        // Test getting a valid gateway
        $this->assertSame($this->gateway, $result);
        
        // Test getting an invalid gateway
        $fakeGateway = 'fakeGateway';
        
        $this->setExpectedException(
            'MMG\\Model\\Exception',
            "Gateway '$fakeGateway' does not exist."
        );
        
        $gateway = $method->invoke($mapper, $fakeGateway);
    } // END function test_getGateway
    
    /**
     * Provides data for testing _getModelInstance().
     *
     * @return  array
     */
    public function provide_getModelInstance()
    {
        $model = $this->_getMockModel(array(
            'id'    => 1,
            'name'  => 'One',
        ));
        
        return array(
            'scalar-identity' => array(
                $model,
                get_class($model),
                $model->getIdentity(),
            ),
            'array-identity' => array(
                $model,
                get_class($model),
                array('id' => $model->getIdentity()),
            ),
            'additional-criteria' => array(
                null,
                get_class($model),
                $model->toArray(),
            ),
            'non-matching-criteria' => array(
                null,
                get_class($model),
                'abc123',
            ),
        );
    } // END function provide_getModelInstance
    
    /**
     * Ensure that _getModelInstance() returns existing instances as expected.
     *
     * @param   Model|null $expected
     * @param   string $class
     * @param   mixed $identity
     * @return  void
     * @dataProvider    provide_getModelInstance
     */
    public function test_getModelInstance($expected, $class, $identity)
    {
        $mapper = $this->_getMockMapper();
        $reflection = new ReflectionClass($mapper);
        
        if ($expected instanceof Model) {
            $property = $reflection->getProperty('_modelInstances');
            $property->setAccessible(true);
            $property->setValue(array(
                get_class($expected) => array(
                    $expected->getIdentity() => $expected
                )
            ));
        }
        
        $method = $reflection->getMethod('_getModelInstance');
        $method->setAccessible(true);
        $result = $method->invoke($mapper, $class, $identity);
        
        $this->assertEquals($expected, $result);
    } // END function test_getModelInstance
    
    /**
     * Provides data for testing conversion of DateTime objects to strings.
     *
     * @return  array
     */
    public function provide_convertDateTime()
    {
        return array(
            'with-time' => array(
                '2013-07-04 10:02:54',
                new DateTime('2013-07-04 10:02:54')
            ),
            'without-time' => array(
                '2013-07-04',
                new DateTime('2013-07-04')
            ),
        );
    } // END function provide_convertDateTime
    
    /**
     * Test converting DateTime objects to string for storage.
     *
     * @param   string $expected The expected date string as 'Y-m-d H:i:s'.
     * @param   DateTime $date The DateTime object to convert.
     * @return  string
     * @dataProvider    provide_convertDateTime
     */
    public function test_convertDateTime($expected, DateTime $date)
    {
        $mapper = $this->_getMockMapper();
        
        $reflection = new ReflectionClass($mapper);
        $method = $reflection->getMethod('_convertDateTime');
        $method->setAccessible(true);
        $result = $method->invoke($mapper, $date);
        
        $this->assertEquals($expected, $result);
    } // END function test_convertDateTime
    
    /**
     * Return a mock model with specified data.
     *
     * @param   array $data
     * @return  Model
     */
    protected function _getMockModel(array $data = array())
    {
        if (!class_exists('MockModel' . $this->_classSuffix, false)) {
            $model = $this->getMockBuilder('MMG\\Model\\Model')
                 ->setMockClassName('Mock_Model_' . $this->_classSuffix)
                 ->setMethods(array('save', 'delete'))
                 ->setConstructorArgs(array($data))
                 ->getMock();
        } else {
            $mockClass = 'MockModel' . $this->_classSuffix;
            $model = new $mockClass($data);
        }
        
        return $model;
    } // END function _getMockModel
    
    /**
     * Return a mock mapper.
     *
     * @param   array $options
     * @return  void
     */
    protected function _getMockMapper(
        $options = array(), 
        $methods = array('_mapToSearchGateway', '_mapToGateways', '_mapToModel')
    )
    {
        if (!class_exists("MockModel{$this->_classSuffix}Mapper", false)) {
            $mapper = $this->getMockBuilder('MMG\\Model\\Mapper\\Mapper')
                 ->setMockClassName("MockModel{$this->_classSuffix}Mapper")
                 ->setMethods($methods)
                 ->setConstructorArgs(array($options))
                 ->getMock();
        } else {
            $mockClass = "MockModel{$this->_classSuffix}Mapper";
            $mapper = new $mockClass;
        }
        
        $this->gateway = $this->getMockBuilder('MMG\\Model\\Gateway\\Pdo')
            ->setMethods(array('_initPdo', 'create', 'read', 'update', 'delete'))
            ->getMock();
        
        Mapper::addGateway($this->gatewayName, $this->gateway);
        
        $reflection = new ReflectionClass($mapper);
        
        $property = $reflection->getProperty('_gateway');
        $property->setAccessible(true);
        $property->setValue($mapper, $this->gatewayName);
        
        return $mapper;
    } // END function _getMockMapper
    
} // END class MapperTest
