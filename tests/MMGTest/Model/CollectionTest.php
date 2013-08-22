<?php
/**
 * Tests for model collection class
 *
 * @category    MMG
 * @package     MMG/Model
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
namespace MMGTest\Model;

/**
 * @todo    Setup autoloading and remove this include.
 */
require_once dirname(__FILE__) . '/../../../library/MMG/Model/Collection.php';
require_once dirname(__FILE__) . '/../../../library/MMG/Model.php';

use PHPUnit_Framework_TestCase as TestCase;
use MMG\Model\Model;
use MMG\Model\Collection;
use ReflectionClass;

/**
 * Tests for model collection class
 *
 * @category    MMG
 * @package     MMG/Model
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class CollectionTest extends TestCase
{
    
    /**
     * @var Collection
     */
    public $collection;
    
    /**
     * undocumented class variable
     *
     * @var string
     */
    public $models = array();
    
    /**
     * Suffix to ensure unique model class names
     *
     * @var string
     */
    protected $_modelSuffix;
    
    /**
     * Setup the test
     *
     * @return  void
     */
    public function setUp()
    {
        $this->_modelSuffix = uniqid();
        
        $this->models = array(
             $this->_getMockModel(array('id' => 1, 'name' => 'one')),
             $this->_getMockModel(array('id' => 2, 'name' => 'two')),
             $this->_getMockModel(array('id' => 3, 'name' => 'three')),
        );
        
        $this->collection = new Collection($this->models);
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
     * Provides data for testing the constructor
     *
     * @return  array
     */
    public function provide__construct()
    {
        $expected = $this->models;
        
        return array(
            array($expected, $this->models),
        );
    } // END function provide__construct
    
    /**
     * Constructor test
     *
     * @param   array $expected
     * @param   array $models
     * @return  void
     * @dataProvider    provide__construct
     */
    public function test__construct(array $expected, array $models)
    {
        $collection = $this->_getMockCollection($models);
        $reflection = new ReflectionClass($collection);
        $property = $reflection->getProperty('_models');
        $property->setAccessible(true);
        
        $actual = $property->getValue($collection);
        
        $this->assertEquals($expected, $actual);
    } // END function test__construct
    
    /**
     * Ensure that models can be pushed onto the collection.
     *
     * @return  void
     */
    public function testPush()
    {
        $model1 = $this->_getMockModel(array('id' => 1, 'name' => 'one'));
        $model2 = $this->_getMockModel(array('id' => 2, 'name' => 'two'));
        
        $expected1 = array();
        $expected2 = array($model1);
        $expected3 = array($model1, $model2);
        
        $collection = $this->_getMockCollection();
        $reflection = new ReflectionClass($collection);
        $property = $reflection->getProperty('_models');
        $property->setAccessible(true);
        
        $actual1 = $property->getValue($collection);
        
        $collection->push($model1);
        $actual2 = $property->getValue($collection);
        
        $collection->push($model2);
        $actual3 = $property->getValue($collection);
        
        $this->assertEquals($expected1, $actual1);
        $this->assertEquals($expected2, $actual2);
        $this->assertEquals($expected3, $actual3);
    } // END function testPush
    
    /**
     * Ensure that models can be popped of the collection.
     *
     * @return  void
     */
    public function testPop()
    {
        $actual = $this->collection->pop();
        
        $this->assertSame($this->models[2], $actual);
    } // END function testPop
    
    /**
     * Test prepending a model to the collection.
     *
     * @return  void
     */
    public function testUnshift()
    {
        $expected = $this->_getMockModel(array(
            'id' => 999, 'name' => 'nine ninety nine'
        ));
        
        $this->collection->unshift($expected);
        
        $reflection = new ReflectionClass($this->collection);
        $property = $reflection->getProperty('_models');
        $property->setAccessible(true);
        $models = $property->getValue($this->collection);
        $actual = $models[0];
        
        $this->assertSame($expected, $actual);
    } // END function testUnshift
    
    /**
     * Test that the first model can be shifted off the collection.
     *
     * @return  void
     */
    public function testShift()
    {
        $expected = $this->models[0];
        $actual = $this->collection->shift();
        
        $this->assertSame($expected, $actual);
    } // END function testShift
    
    /**
     * Return the key of the current model in the collection.
     *
     * @return  void
     */
    public function testKey()
    {
        $expected = key($this->models);
        $actual = $this->collection->key();
        
        $this->assertEquals($expected, $actual);
    } // END function testKey
    
    /**
     * Ensure the collection is countable.
     *
     * @return  void
     */
    public function testCount()
    {
        $expected = count($this->models);
        $actual = $this->collection->count();
        
        $this->assertEquals($expected, $actual);
    } // END function testCount
    
    /**
     * Find a model in the collection by its identity.
     *
     * @return  void
     */
    public function testGetByIdentity()
    {
        $models = array(
            $this->_getMockModel(array('id' => 1, 'name' => 'one')),
            $this->_getMockModel(array('id' => 2, 'name' => 'two')),
            $this->_getMockModel(array('id' => 3, 'name' => 'three')),
        );
        
        $collection = $this->_getMockCollection($models);
        
        $expected = $models[1];
        $actual = $collection->getByIdentity(2);
        
        $this->assertSame($expected, $actual);
        $this->assertNull($collection->getByIdentity(9));
    } // END function testGetByIdentity
    
    /**
     * Tests the __call override
     *
     * @return  void
     */
    public function test__call()
    {
        $lastModel = end($this->models);
        $expected = $lastModel->getIdentity();
        $actual = $this->collection->getIdentity();
        
        $this->assertEquals($expected, $actual);
    } // END function test__call
    
    /**
     * Provides data for testing _callModelMethod().
     *
     * @return  array
     */
    public function provide_callModelMethod()
    {
        return array(
            'none'  => array(array()),
            'one'   => array(array_fill(0, 1, 'test')),
            'two'   => array(array_fill(0, 2, 'test')),
            'three' => array(array_fill(0, 3, 'test')),
            'four'  => array(array_fill(0, 4, 'test')),
            'five'  => array(array_fill(0, 5, 'test')),
        );
    } // END function provide_callModelMethod
    
    /**
     * Ensure model methods are called properly.
     *
     * @param   $args
     * @return  void
     * @dataProvider    provide_callModelMethod
     */
    public function test_callModelMethod($args = array())
    {
        $reflection = new ReflectionClass($this->collection);
        $method = $reflection->getMethod('_callModelMethod');
        $method->setAccessible(true);
        
        $model = $this->collection->pop();
        $mockMethod = $model->expects($this->once())->method('save');
        
        switch (count($args)) {
            case 0:
                break;
            case 1:
                $mockMethod->with($this->anything());
                break;
            case 2:
                $mockMethod->with($this->anything(), $this->anything());
                break;
            case 3:
                $mockMethod->with(
                    $this->anything(),
                    $this->anything(),
                    $this->anything()
                );
                break;
            case 4:
                $mockMethod->with(
                    $this->anything(),
                    $this->anything(),
                    $this->anything(),
                    $this->anything()
                );
                break;
            default:
                $mockMethod->with(
                    $this->anything(),
                    $this->anything(),
                    $this->anything(),
                    $this->anything(),
                    $this->anything()
                );
        }
        
        $method->invokeArgs(
            $this->collection, 
            array($model, 'save', $args)
        );
    } // END function test_callModelMethod
    
    /**
     * Return a mock model with specified data.
     *
     * @param   array $data
     * @return  Model
     */
    protected function _getMockModel(array $data = array())
    {
        if (!class_exists('MockModel' . $this->_modelSuffix, false)) {
            $model = $this->getMockBuilder('MMG\\Model\\Model')
                 ->setMockClassName('MockModel' . $this->_modelSuffix)
                 ->setMethods(array('save', 'delete'))
                 ->setConstructorArgs(array($data))
                 ->getMock();
        } else {
            $mockClass = 'MockModel' . $this->_modelSuffix;
            $model = new $mockClass($data);
        }
        
        return $model;
    } // END function _getMockModel
    
    /**
     * Return a mock collection with specified models.
     *
     * @param   array $models Array of model instances
     * @return  Collection
     * 
     */
    protected function _getMockCollection(array $models = array())
    {
        $collection = $this->getMockBuilder('MMG\\Model\\Collection')
            ->setMethods(null)
            ->setConstructorArgs(array($models))
            ->getMock();
        
        return $collection;
    } // END function _getMockCollection
    
} // END class CollectionTest