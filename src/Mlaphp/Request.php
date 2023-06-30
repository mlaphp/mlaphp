<?php
/**
 * This file is part of "Modernizing Legacy Applications in PHP".
 *
 * If you are using PHP 8.1 or later, this class may not work for you.
 * Consider using Request81 as a drop-in replacement.
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
 * @package mlaphp/mlaphp
 */
class Request
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
     * A **reference** to $GLOBALS. We keep this so we can have late access to
     * $_SESSION.
     *
     * @var array
     */
    protected $globals;

    /**
     * A **reference** to $_SESSION. We use a reference because PHP uses
     * $_SESSION for all its session_*() functions.
     *
     * @var array
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param array $globals A reference to $GLOBALS.
     */
    public function __construct(&$globals)
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

        // retain a reference to the $globals param, not to $GLOBALS directly
        $this->globals = &$globals;

        // copy superglobals into properties
        $properties = array(
            'cookie' => '_COOKIE',
            'env' => '_ENV',
            'files' => '_FILES',
            'get' => '_GET',
            'post' => '_POST',
            'request' => '_REQUEST',
            'server' => '_SERVER',
        );

        foreach ($properties as $property => $superglobal) {
            if (isset($globals[$superglobal])) {
                $this->$property = $globals[$superglobal];
            }
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

        if (! isset($this->globals['_SESSION'])) {
            throw new DomainException('$_SESSION is not set');
        }

        if (! isset($this->session)) {
            $this->session = &$this->globals['_SESSION'];
        }

        return $this->session;
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

        if (isset($this->globals['_SESSION'])) {
            $this->session = &$this->globals['_SESSION'];
        }

        return isset($this->session);
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

        $this->session = null;
        unset($this->globals['_SESSION']);
    }
}
