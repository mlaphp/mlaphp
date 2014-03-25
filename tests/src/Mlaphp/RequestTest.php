<?php
namespace Mlaphp;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function newRequest()
    {
        return new Request($GLOBALS);
    }

    public function testCookie()
    {
        $_COOKIE['foo'] = 'bar';
        $request = $this->newRequest();
        $this->assertSame('bar', $request->cookie['foo']);
    }

    public function testEnv()
    {
        $_ENV['foo'] = 'bar';
        $request = $this->newRequest();
        $this->assertSame('bar', $request->env['foo']);
    }

    public function testFiles()
    {
        $_FILES['foo'] = 'bar';
        $request = $this->newRequest();
        $this->assertSame('bar', $request->files['foo']);
    }

    public function testGet()
    {
        $_GET['foo'] = 'bar';
        $request = $this->newRequest();
        $this->assertSame('bar', $request->get['foo']);
    }

    public function testPost()
    {
        $_POST['foo'] = 'bar';
        $request = $this->newRequest();
        $this->assertSame('bar', $request->post['foo']);
    }

    public function testRequest()
    {
        $_REQUEST['foo'] = 'bar';
        $request = $this->newRequest();
        $this->assertSame('bar', $request->request['foo']);
    }

    public function testServer()
    {
        $_SERVER['foo'] = 'bar';
        $request = $this->newRequest();
        $this->assertSame('bar', $request->server['foo']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSession()
    {
        $request = $this->newRequest();

        // session not started yet
        $this->assertFalse(isset($_SESSION));
        $this->assertFalse(isset($request->session));
        
        // session started
        session_start();
        $this->assertTrue(isset($_SESSION));

        // check the reference from $_SESSION to $request ...
        $_SESSION['foo'] = 'bar';
        $this->assertSame('bar', $request->session['foo']);

        // ... and from $request back to $_SESSION
        $request->session['baz'] = 'dib';
        $this->assertSame('dib', $_SESSION['baz']);

        // unset both property and superglobals
        unset($request->session);
        $this->assertFalse(isset($_SESSION));
        $this->assertFalse(isset($request->session));

    }

    /**
     * @runInSeparateProcess
     */
    public function testIssetLinksToSession()
    {
        $request = $this->newRequest();

        // session not started yet
        $this->assertFalse(isset($_SESSION));
        $this->assertFalse(isset($request->session));
        
        // session started
        session_start();

        // this should attach property to $_SESSION
        $this->assertTrue(isset($request->session));
        $request->session['baz'] = 'dib';
        $this->assertSame('dib', $_SESSION['baz']);
    }

    public function testGetSessionBeforeStarted()
    {
        $request = $this->newRequest();
        $this->assertFalse(isset($GLOBALS['_SESSION']));
        $this->setExpectedException('DomainException');
        $request->session['foo'] = 'bar';
    }

    public function testGetWrongName()
    {
        $request = $this->newRequest();
        $this->setExpectedException('InvalidArgumentException');
        $request->notSession;
    }

    public function testIssetWrongName()
    {
        $request = $this->newRequest();
        $this->setExpectedException('InvalidArgumentException');
        isset($request->notSession);
    }

    public function testUnsetWrongName()
    {
        $request = $this->newRequest();
        $this->setExpectedException('InvalidArgumentException');
        unset($request->notSession);
    }
}
