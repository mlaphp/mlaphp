<?php
namespace Mlaphp;

class DiTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->di = new Di;
    }
    
    public function testMagic()
    {
        $this->assertFalse(isset($this->di->foo));
        $this->di->foo = 'bar';
        $this->assertTrue(isset($this->di->foo));
        $this->assertSame('bar', $this->di->foo);
        unset($this->di->foo);
        $this->assertFalse(isset($this->di->foo));
    }
    
    public function testSetHasGet()
    {
        $this->assertFalse($this->di->has('mock'));
        
        $this->di->set('mock', function () {
            return new \StdClass;
        });
        
        $this->assertTrue($this->di->has('mock'));
        
        $instance1 = $this->di->get('mock');
        $this->assertInstanceOf('StdClass', $instance1);
        
        $instance2 = $this->di->get('mock');
        $this->assertSame($instance1, $instance2);

        $instance3 = $this->di->newInstance('mock');
        $this->assertFalse($instance2 === $instance3);
    }
    
    public function testGetNoSuchInstance()
    {
        $this->setExpectedException('UnexpectedValueException');
        $this->di->get('NoSuchInstance');
    }
}
