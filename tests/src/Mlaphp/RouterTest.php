<?php
namespace Mlaphp;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    protected $router;

    protected $pages_dir;

    public function setUp()
    {
        $this->pages_dir = dirname(dirname(__DIR__)) . '/pages';
        $this->router = new Router($this->pages_dir);
        $this->router->setFront('front-controller.php');
        $this->router->setHomeRoute('/hello.php');
        $this->router->setNotFoundRoute('/page-not-found.php');
    }

    public function testMatchWithoutFront()
    {
        $expect = "{$this->pages_dir}/hello.php";
        
        $actual = $this->router->match('/');
        $this->assertSame($expect, $actual);
        
        $actual = $this->router->match('/hello.php');
        $this->assertSame($expect, $actual);
    }

    public function testMatchStripsFront()
    {
        $expect = "{$this->pages_dir}/hello.php";

        $actual = $this->router->match('/front-controller.php');
        $this->assertSame($expect, $actual);

        $actual = $this->router->match('/front-controller.php/');
        $this->assertSame($expect, $actual);
    }

    public function testMatchNotFoundUsingFile()
    {
        $expect = $this->pages_dir . '/page-not-found.php';
        $actual = $this->router->match('/no-such-file');
        $this->assertSame($expect, $actual);
    }

    public function testMatchNotFoundUsingClass()
    {
        $expect = "Controller\Http404";
        $this->router->setNotFoundRoute($expect);
        $actual = $this->router->match('/no-such-file');
        $this->assertSame($expect, $actual);
    }

    public function testMatchMappedClass()
    {
        $this->router->setRoutes(array(
            '/controller-name' => 'ControllerClass',
        ));

        $expect = 'ControllerClass';
        $actual = $this->router->match('/controller-name');
        $this->assertSame($expect, $actual);
    }

    public function testMatchMappedFile()
    {
        $this->router->setRoutes(array(
            '/hello.php' => '/other.php',
        ));

        $expect = "{$this->pages_dir}/other.php";
        $actual = $this->router->match('/hello.php');
        $this->assertSame($expect, $actual);
    }

    public function testNoPagesDir()
    {
        $router = new Router;
        $this->setExpectedException('RuntimeException');
        $router->match('/hello.php');
    }
}
