<?php
/**
 * Tests for model abstract
 *
 * @category    MMG
 * @package     MMG/Model
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 * @since       File available since release 0.0.1
 * @filesource
 */
namespace MMGTest\Model;

/**
 * @todo    Setup autoloading and remove this include.
 */
require_once dirname(__FILE__) . '/../../library/MMG/Model.php';
require_once dirname(__FILE__) . '/../../library/MMG/Model/Exception.php';
require_once dirname(__FILE__) . '/../../library/MMG/Model/Mapper.php';
require_once dirname(__FILE__) . '/../../library/MMG/Model/NestedTrait.php';

use PHPUnit_Framework_TestCase as TestCase;
use MMG\Model\Model;
use MMG\Model\Exception;
use MMG\Model\Mapper\Mapper;
use MMG\Model\NestedTrait;
use DateTime;
use ReflectionClass, ReflectionObject, ReflectionMethod, ReflectionProperty;

/**
 * Tests for model abstract
 *
 * @category    MMG
 * @package     MMG/Model
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class ModelTest extends TestCase
{
    /**
     * @var Model
     */
    public $model;

    /**
     * Test model data
     *
     * @var array
     */
    public $modelData = array(
        'id'    => 123,
        'name'  => 'test name'
    );

    /**
     * Suffix to ensure unique class names
     *
     * @var string
     */
    protected $_classSuffix;

    /**
     * Set up the test
     * 
     * @return  void
     */
    public function setUp()
    {
        $this->_classSuffix = uniqid();
        $this->model = $this->_getMockModel($this->modelData);
    } // END function setUp

    /**
     * Tear down the test
     * 
     * @return  void
     */
    public function tearDown()
    {
    } // END function tearDown

    /**
     * Ensure that model data can be set
     * from the constructor.
     */
    public function testModelDataSetFromConstructor()
    {
        $model = new Model($this->modelData);
        
        $this->assertEquals($this->modelData['id'], $model->id);
        $this->assertEquals($this->modelData['name'], $model->name);

        // Test passing junk.
        $this->setExpectedException(
            '\\Exception'
        );
        
        new Model('fail');
    } // END function testModelDataSetFromConstructor

    /**
     * Ensure that model data is not required
     * in the constructor.
     */
    public function testModelDataNotRequiredInConstructor()
    {
        $data = array('id' => null, 'name' => null);
        $model = $this->_getMockModel($data, true);

        $this->assertNull($model->id);
        $this->assertNull($model->name);
    } // END function testModelDataNotRequiredInConstructor

    /**
     * Ensure that the data can be alternatively passed as an option
     */
    public function testDataPassedAsOption()
    {
        $expected = array('test1' => 'some stuff', 'test2' => 'more stuff');
        $mockClass = 'MockModel' . $this->_classSuffix;
        $model = new $mockClass(array('data' => $expected));
        
        $reflection = new ReflectionClass($model);
        $property = $reflection->getProperty('_data');
        $property->setAccessible(true);
        $actual = $property->getValue($model);
        
        $this->assertEquals($expected, $actual);
        
        // Test passing junk in 'data' param
        $this->setExpectedException(
            'MMG\\Model\\Exception',
            'Data must be an array.'
        );
        
        new Model(array('data' => 'fail'));
    } // END function testDataPassedAsOption
    
    /**
     * Set the mapper class as an option.
     *
     * @return  void
     */
    public function testMapperClassPassedAsOption()
    {
        $expected = 'MyMapper';
        
        $mockClass = 'MockModel' . $this->_classSuffix;
        $model = new $mockClass(array('mapperClass' => $expected));
        
        $reflection = new ReflectionClass($model);
        $property = $reflection->getProperty('_mapperClass');
        $property->setAccessible(true);
        $actual = $property->getValue($model);
        
        $this->assertEquals($expected, $actual);
    } // END function testMapperClassPassedAsOption
    
    /**
     * Set the mappr instance as an option.
     *
     * @return  void
     */
    public function testMapperPassedAsOption()
    {
        $expected = $this->getMockForAbstractClass('MMG\\Model\\Mapper\\Mapper');
        
        $mockClass = 'MockModel' . $this->_classSuffix;
        $model = new $mockClass(array('mapper' => $expected));
        
        $reflection = new ReflectionClass($model);
        
        $property = $reflection->getProperty('_mapper');
        $property->setAccessible(true);
        $actualMapper = $property->getValue($model);
        
        $property = $reflection->getProperty('_mapperClass');
        $property->setAccessible(true);
        $actualClass = $property->getValue($model);
        
        $this->assertEquals($expected, $actualMapper);
        $this->assertEquals(get_class($expected), $actualClass);
    } // END function testMapperPassedAsOption

    /**
     * Tests the __unset method of the Model class
     * 
     * @return  void
     */
    public function test__unset()
    {
        unset($this->model->name);
        $this->assertNull($this->model->name);
    } // END function test__unset

    /**
     * Ensure that only properties in the
     * _data array can be set.
     */
    public function testCannotSetUndefinedProperty()
    {
        $prop = 'nonexistentProp';
        
        $this->setExpectedException(
            'MMG\Model\Exception',
            "Undefined property: $prop"
        );

        $result = $this->model->$prop;
    } // END function testCannotSetUndefinedProperty
    
    /**
     * Ensure that only properties in the
     * _data array are "getable".
     *
     * @return  void
     */
    public function testCannotGetUndefinedProperty()
    {
        $this->setExpectedException(
            'MMG\Model\Exception',
            'You cannot set new properties on this object'
        );

        $this->model->abc = 123;
    } // END function testCannotGetUndefinedProperty
    
    /**
     * Ensure setter methods are used when available.
     *
     * @return  void
     */
    public function testSetterMethodCalled()
    {
        $value = 'abc123';
        
        // Should call $model->setIdentity($value);
        $this->model->identity = $value;
        
        $this->assertEquals($value, $this->model->getIdentity());
    } // END function testSetterMethodCalled
    
    /**
     * Ensure getter methods are used when available.
     *
     * @return  void
     */
    public function testGetterMethodCalled()
    {
        $expected = $this->model->getIdentity();
        
        // Should call $model->getIdentity();
        $actual = $this->model->identity;
        
        $this->assertEquals($expected, $actual);
    } // END function testGetterMethodCalled

    /**
     * Data provider for testToArray
     */
    public function provideToArray()
    {
        $nested = $this->_getMockModel(array(
            'id' => 456,
            'name' => 'nested',
        ));
        
        $modelArray = array(
            $this->_getMockModel(
                array('id' => 111, 'name' => 'array model 1')
            ),
            $this->_getMockModel(
                array('id' => 222, 'name' => 'array model 2')
            ),
        );
        
        return array(
            array(
                array(
                    'id' => 123,
                    'name' => 'test name',
                    'nested' => $nested,
                    'modelArray' => $modelArray,
                    'dateTime' => new DateTime('2012-02-14 16:34:54'),
                    'otherDate' => new DateTime('2012-03-22'),
                    'object' => (object) array('id' => 'abc123', 'name' => 'myObj'),
                ),
                array(
                    'id'    => 123,
                    'name'  => 'test name',
                    'nested' => $nested,
                    'modelArray' => $modelArray,
                    'dateTime' => new DateTime('2012-02-14 16:34:54'),
                    'otherDate' => new DateTime('2012-03-22'),
                    'object' => (object) array('id' => 'abc123', 'name' => 'myObj'),
                ),
            ),
        );

    } // END function provideToArray

    /**
     * Ensure the model can be exported
     * to a nested array.
     *
     * @param   array $expected
     * @param   array $data
     * @return  void
     * @dataProvider provideToArray
     */
    public function testToArray($expected, $data)
    {
        $model = $this->_getMockModel($data);
        
        $this->assertEquals($expected, $model->toArray());

    } // END function testToArray

    /**
     * Data provider for testFromArray
     */
    public function provideFromArray()
    {
        return array(
            array(
                'data' => array(
                    'id' => 123,
                    'name' => 'test name',
                    'dateTime' => new DateTime('2012-02-14 16:34:54'),
                    'unusedProp' => 'test unused'
                ),
                'expected' => array(
                    'id' => 123,
                    'name' => 'test name',
                    'dateTime' => new DateTime('2012-02-14 16:34:54'),
                ),
            ),
        );

    } // END function provideFromArray

    /**
     * @dataProvider provideFromArray
     */
    public function testFromArray($data, $expected)
    {
        $schema = array_fill_keys(array_keys($expected), null);
        $model = $this->_getMockModel($schema);
        $model->fromArray($data);

        $this->assertEquals($expected, $model->toArray());

    } // END function testFromArray

    /**
     * Tests the magic method __isset
     *
     * @param boolean $expected
     * @param string $name
     * @dataProvider provide__isset
     */
    public function test__isset($expected, $name)
    {
        $this->assertSame($expected, $this->model->__isset($name));
    } // END function test__isset

    /**
     * Provides data to use for testing the magic method __isset on the
     * Model class
     */
    public function provide__isset()
    {
        return array(
            'true case' => array(true, 'name'),
            'false case' => array(false, 'fake'),
        );
    } // END function provide__isset
    
    /**
     * Test setting the mapper class.
     *
     * @return  void
     */
    public function testSetMapperClass()
    {
        $expected = 'MyMapper';
        
        $return = $this->model->setMapperClass($expected);
        
        $property = new ReflectionProperty(
            get_class($this->model), 
            '_mapperClass'
        );
        $property->setAccessible(true);
        $result = $property->getValue($this->model);
        
        $this->assertSame($this->model, $return); // test returns self
        $this->assertEquals($expected, $result);
        
    } // END function testSetMapperClass

    /**
     * Ensure that getMapperClass() returns the correct class name
     */
    public function testGetMapperClass()
    {
        $expected = "MockModel{$this->_classSuffix}Mapper"; // See _getMockModel to understand the class name
        
        $property = new ReflectionProperty(
            get_class($this->model), 
            '_mapperClass'
        );
        $property->setAccessible(true);
        $property->setValue($this->model, null);
        
        $method = new ReflectionMethod(get_class($this->model), 'getMapperClass');
        $method->setAccessible(true);
        $mapperClass = $method->invoke($this->model);

        $this->assertEquals($expected, $mapperClass);
    } // END public function testGetMapperClass

    /**
     * Ensure that we get a mapper instance from getMapper()
     */
    public function testGetMapper()
    {
        $property = new ReflectionProperty(get_class($this->model), '_mapper');
        $property->setAccessible(true);
        $expected = $property->getValue($this->model);

        $this->assertSame($expected, $this->model->getMapper());
        
        // test when mapper is not already set
        $property->setValue($this->model, null);
        $expected = get_class($this->model) . 'Mapper';
        
        $this->assertInstanceOf($expected, $this->model->getMapper());
        
    } // END public function testGetMapper

    /**
     * Tests the save method of the new model abstract class
     */
    public function testSave()
    {
        $mapper = $this->model->getMapper();

        $mapper->expects($this->once())
            ->method('save')
            ->with($this->equalTo($this->model))
            ->will($this->returnValue(null));

        $this->model->save();

    } // END function testSave

    /**
     * Provides data to use for testing the delete method of the abstract model
     *
     * @return array
     */
    public function provideDelete()
    {
        return array(
            array(true),
            array(false),
        );

    } // END function provideDelete

    /**
     * Tests the delete method of the abstract model
     *
     * @dataProvider provideDelete
     */
    public function testDelete($expected)
    {
        $mapper = $this->model->getMapper();

        $mapper->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($this->model))
            ->will($this->returnValue($expected));

        $result = $this->model->delete();

        $this->assertSame($expected, $result);

    } // END function testDelete
    
    /**
     * Ensure the model can be uniquely identified.
     *
     * @return  void
     */
    public function testGetIdentity()
    {
        $expected = $this->modelData['id'];
        $actual = $this->model->getIdentity();
        
        $this->assertEquals($expected, $actual);
    } // END function testGetIdentity
    
    /**
     * Test getting and setting nested data.
     *
     * @return  void
     */
    public function testNestedData()
    {
        $model = new NestableModel;
        
        $prop1 = 'abc';
        $prop2 = 'def';
        $nested = 'qux';
        $arrNested = array('one', 'two', 'three');
        
        $model->prop1 = $prop1;
        $model->foo->bar->baz = $nested;
        $model->oneFish->twoFish = $arrNested;
        $model->prop2 = $prop2;
        
        $this->assertEquals($prop1, $model->prop1);
        $this->assertEquals($nested, $model->foo->bar->baz);
        $this->assertEquals($arrNested, $model->oneFish->twoFish);
        $this->assertEquals($prop2, $model->prop2);
        
    } // END function testNestedData

    /**
     * Create a mock model with optional data
     *
     * @param   mixed $data
     * @param   boolean $schemeOnly
     * @return  Model
     */
    protected function _getMockModel($data = null, $schemeOnly = false)
    {
        if (!class_exists('MockModel' . $this->_classSuffix, false)) {
            $mock = $this->getMockBuilder('MMG\\Model\\Model')
                ->setMockClassName('MockModel' . $this->_classSuffix)
                ->setMethods(null)
                ->getMock();
        } else {
            $mockClass = 'MockModel' . $this->_classSuffix;
            $mock = new $mockClass;
        }
        
        if (!class_exists('MockModelMapper' . $this->_classSuffix, false)) {
            $mapperMock = $this->getMockBuilder('MMG\\Model\\Mapper\\Mapper')
                ->setMockClassName("MockModel{$this->_classSuffix}Mapper")
                ->setMethods(array(
                    '_mapToSearchGateway', '_mapToGateways', '_mapToModel', 
                    'save', 'delete'
                ))
                ->getMock();
        } else {
            $mapperMockClass = "MockModel{$this->_classSuffix}Mapper";
            $mapperMock = new $mapperMockClass;
        }
        
        $mock->setMapper($mapperMock);

        if (isset($data['data'])) {
            $data = $data['data'];
        }

        $dataScheme = array();
        if (is_array($data)) {
            $dataScheme = array_fill_keys(array_keys($data), null);
        }

        $reflector = new ReflectionObject($mock);
        $dataProperty = $reflector->getProperty('_data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($mock, $dataScheme);

        if (!$schemeOnly) {
            $mock->__construct(array('data' => $data));
        }

        return $mock;
    }

} //END class ModelTest

class NestableModel extends Model
{
    use NestedTrait;
    
    /**
     * @param   mixed $prop1
     * @return  void
     */
    public function setProp1($value)
    {
        $this->_data['prop1'] = $value;
    } // END function setProp1
    
    /**
     * @return  mixed
     */
    public function getProp1()
    {
        return $this->_data['prop1'];
    } // END function getProp1
    
} // END class NestableModel
