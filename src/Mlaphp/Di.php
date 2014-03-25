<?php
/**
 * This file is part of "Modernizing Legacy Applications in PHP".
 *
 * @copyright 2014 Paul M. Jones <pmjones88@gmail.com>
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Mlaphp;

use UnexpectedValueException;

/**
 * A dependency injection container.
 */
class Di
{
    /**
     * A registry of callables to create object instances.
     *
     * @var array
     */
    protected $callables = array();

    /**
     * A registry of shared instances created by the callables.
     *
     * @var array
     */
    protected $instances = array();

    /**
     * Variables used as parameters; accessed as magic properties.
     *
     * @var array
     */
    protected $variables = array();

    /**
     * Constructor.
     *
     * @param array $variables A an existing array of variables to be used as
     * magic properties, typically $GLOBALS.
     */
    public function __construct(array $variables = array())
    {
        $this->variables = $variables;
    }

    /**
     * Gets a magic property.
     *
     * @param string $key The property name.
     * @return null
     */
    public function __get($key)
    {
        return $this->variables[$key];
    }

    /**
     * Sets a magic property.
     *
     * @param string $key The property name.
     * @param mixed $val The property value.
     * @return null
     */
    public function __set($key, $val)
    {
        $this->variables[$key] = $val;
    }

    /**
     * Is a magic property set?
     *
     * @param string $key The property name.
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->variables[$key]);
    }

    /**
     * Unsets a magic property.
     *
     * @param string $key The property name.
     * @return bool
     */
    public function __unset($key)
    {
        unset($this->variables[$key]);
    }

    /**
     * Sets a callable to create an object by name; removes any existing
     * shared instance under that name.
     *
     * @param string $name The object name.
     * @param callable $callable A callable that returns an object.
     * @return null
     */
    public function set($name, $callable)
    {
        $name = ltrim($name, '\\');
        $this->callables[$name] = $callable;
        unset($this->instances[$name]);
    }

    /**
     * Is a named callable defined?
     *
     * @return bool
     */
    public function has($name)
    {
        $name = ltrim($name, '\\');
        return isset($this->callables[$name]);
    }

    /**
     * Gets a shared instance by object name; if it has not been created yet,
     * its callable will be invoked and the instance will be retained.
     *
     * @param string $name The name of the shared instance to retrieve.
     * @return object The shared object instance.
     */
    public function get($name)
    {
        $name = ltrim($name, '\\');
        if (! isset($this->instances[$name])) {
            $this->instances[$name] = $this->newInstance($name);
        }
        return $this->instances[$name];
    }

    /**
     * Returns a new instance using the named callable.
     *
     * @param string $name The name of the callable to invoke.
     * @return object A new object instance.
     * @throws UnexpectedValueException
     */
    public function newInstance($name)
    {
        $name = ltrim($name, '\\');
        if (! $this->has($name)) {
            throw new UnexpectedValueException($name);
        }
        return call_user_func($this->callables[$name], $this);
    }
}
