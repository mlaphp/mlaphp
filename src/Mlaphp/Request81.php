<?php
/**
 * This file is part of "Modernizing Legacy Applications in PHP".
 *
 * @copyright 2014-2023 Paul M. Jones <pmjones88@gmail.com>
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Mlaphp;

use DomainException;
use InvalidArgumentException;

/**
 * A data structure object to encapsulate superglobal references.
 *
 * This version of the Request object is for use on PHP 8.1 and later, but
 * should work back to as far as PHP 5.6 (or even earlier).
 *
 * PHP 5.3, which was the current version when the original Request object was
 * created, allowed passing of $GLOBALS by reference. However, PHP 8.1 does not.
 * That means the original Request object will not work on PHP 8.1 and later,
 * whereas this version does.
 *
 * Note that $_SESSION works slightly differently than from the original Request
 * object. The implementation differences should not have any practical effect
 * when using Request81 as vs. the original Request.
 *
 * @package mlaphp/mlaphp
 */
class Request81
{
    /**
     * A copy of $_COOKIE.
     *
     * @var array
     */
    public $cookie = array();

    /**
     * A copy of $_ENV.
     *
     * @var array
     */
    public $env = array();

    /**
     * A copy of $_FILES.
     *
     * @var array
     */
    public $files = array();

    /**
     * A copy of $_GET.
     *
     * @var array
     */
    public $get = array();

    /**
     * A copy of $_POST.
     *
     * @var array
     */
    public $post = array();

    /**
     * A copy of $_REQUEST.
     *
     * @var array
     */
    public $request = array();

    /**
     * A copy of $_SERVER.
     *
     * @var array
     */
    public $server = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        // mention the superglobals by name to invoke auto_globals_jit, thereby
        // forcing them to be populated; cf. <http://php.net/auto-globals-jit>.
        $_COOKIE;
        $_ENV;
        $_FILES;
        $_GET;
        $_POST;
        $_REQUEST;
        $_SERVER;

        if (isset($_COOKIE)) {
            $this->cookie = $_COOKIE;
        }

        if (isset($_ENV)) {
            $this->env = $_ENV;
        }

        if (isset($_FILES)) {
            $this->files = $_FILES;
        }

        if (isset($_GET)) {
            $this->get = $_GET;
        }

        if (isset($_POST)) {
            $this->post = $_POST;
        }

        if (isset($_REQUEST)) {
            $this->request = $_REQUEST;
        }

        if (isset($_SERVER)) {
            $this->server = $_SERVER;
        }
    }

    /**
     * Provides a magic **reference** to $_SESSION.
     *
     * @param string $property The property name; must be 'session'.
     * @return array A reference to $_SESSION.
     * @throws InvalidArgumentException for any $name other than 'session'.
     * @throws DomainException when $_SESSION is not set.
     */
    public function &__get($name)
    {
        if ($name != 'session') {
            throw new InvalidArgumentException($name);
        }

        if (! isset($_SESSION)) {
            throw new DomainException('$_SESSION is not set');
        }

        return $_SESSION;
    }

    /**
     * Provides magic isset() for $_SESSION and the related property.
     *
     * @param string $name The property name; must be 'session'.
     * @return bool
     */
    public function __isset($name)
    {
        if ($name != 'session') {
            throw new InvalidArgumentException;
        }

        return isset($_SESSION);
    }

    /**
     * Provides magic unset() for $_SESSION; unsets both the property and the
     * superglobal.
     *
     * @param string $name The property name; must be 'session'.
     * @return null
     */
    public function __unset($name)
    {
        if ($name != 'session') {
            throw new InvalidArgumentException;
        }

        unset($_SESSION);
    }
}
