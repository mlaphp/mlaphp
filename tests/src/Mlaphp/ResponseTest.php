<?php
namespace Mlaphp;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    protected $response;

    protected $func_result = 'func not called';

    protected $base;

    protected $view;

    public function setUp()
    {
        $this->base = dirname(dirname(__DIR__)) . '/views';
        $this->view = 'response_view.php';
        $this->response = new FakeResponse;
    }

    public function testSetAndGetBase()
    {
        $this->response->setBase($this->base);
        $this->assertSame($this->base, $this->response->getBase());
    }

    public function testSetAndGetView()
    {
        $this->response->setView($this->view);
        $this->assertSame($this->view, $this->response->getView());
    }

    public function testSetAddGetVars()
    {
        $this->response->setVars(array('foo' => 'bar'));
        $this->response->addVars(array('foo' => 'zim', 'dib' => 'gir'));
        $expect = array('foo' => 'zim', 'dib' => 'gir');
        $this->assertSame($expect, $this->response->getVars());
    }

    public function testSetAndGetLastCall()
    {
        $func = array($this, 'responseFunc');
        $this->response->setLastCall($func, 'made last call');
        $expect = array($func, 'made last call');
        $actual = $this->response->getLastCall();
        $this->assertSame($expect, $actual);
    }

    public function testEsc()
    {
        $expect = "&lt;tag&gt;&quot;quote\&#039;apos&amp;amp";
        $actual = $this->response->esc("<tag>\"quote\'apos&amp");
        $this->assertSame($expect, $actual);
    }

    public function testBufferedHeaders()
    {
        $this->response->header('Foo: Bar');
        $this->response->setCookie('cookie', 'value');
        $this->response->setRawCookie('rawcookie', 'rawvalue');
        $expect = array(
            array('header', 'Foo: Bar'),
            array('setcookie', 'cookie', 'value'),
            array('setrawcookie', 'rawcookie', 'rawvalue'),
        );
        $this->assertSame($expect, $this->response->getHeaders());
    }

    public function testGetViewPath()
    {
        $this->response->setBase($this->base);
        $this->response->setView($this->view);
        $expect = $this->base . DIRECTORY_SEPARATOR . $this->view;
        $this->assertSame($expect, $this->response->getViewPath());
    }

    public function testRequireView()
    {
        $this->response->setVars(array('noun' => 'World'));
        $this->response->setBase($this->base);
        $this->response->setView($this->view);
        $output = $this->response->requireView();
        $this->assertSame('Hello World!', $output);
    }

    public function testNoViewToRequire()
    {
        $output = $this->response->requireView();
        $this->assertSame('', $output);
    }

    public function testInvokeLastCall()
    {
        $this->response->setLastCall(array($this, 'responseFunc'), 'made last call');
        $this->response->invokeLastCall();
        $this->assertSame('made last call', $this->func_result);
    }

    public function testNoLastCall()
    {
        $this->response->invokeLastCall();
        $this->assertSame('func not called', $this->func_result);
    }

    public function responseFunc($string)
    {
        $this->func_result = $string;
    }

    public function testSendHeaders()
    {
        $this->response->fakeHeader('Foo: Bar');
        $this->response->sendHeaders();
        $this->assertSame('Foo: Bar', $this->response->fake_headers);
    }

    public function testSend()
    {
        // prep
        $this->response->setBase($this->base);
        $this->response->setView($this->view);
        $this->response->setVars(array('noun' => 'World'));
        $this->response->setLastCall(array($this, 'responseFunc'), 'made last call');
        $this->response->fakeHeader('Foo: Bar');

        // send
        ob_start();
        $this->response->send();
        $output = ob_get_clean();

        // test
        $this->assertSame('Hello World!', $output);
        $this->assertSame('Foo: Bar', $this->response->fake_headers);
        $this->assertSame('made last call', $this->func_result);
    }
}
