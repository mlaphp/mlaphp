<?php
/**
 * This file is part of "Modernizing Legacy Applications in PHP".
 *
 * @copyright 2014-2016 Paul M. Jones <pmjones88@gmail.com>
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Mlaphp;

use RuntimeException;

/**
 * A basic router implementation that converts URL paths to file paths or class
 * names.
 *
 * @package mlaphp/mlaphp
 */
class Router
{
    /**
     * The URL path prefix for the front controller.
     *
     * @var string
     */
    protected $front = '/front.php';

    /**
     * The route value for the home page (URL path `/`).
     *
     * @var string
     */
    protected $home_route = '/index.php';

    /**
     * The route value for when there is no matching route.
     *
     * @var string
     */
    protected $not_found_route = '/not-found.php';

    /**
     * The path to the pages directory.
     *
     * @var string
     */
    protected $pages_dir;

    /**
     * The map of URL paths (keys) to file paths or class names (values).
     *
     * @var array
     */
    protected $routes = array();

    /**
     * Constructor.
     *
     * @param string $pages_dir The path to the pages directory.
     */
    public function __construct($pages_dir = null)
    {
        if ($pages_dir) {
            $this->pages_dir = rtrim($pages_dir, '/');
        }
    }

    /**
     * Sets the URL path prefix for the front controller.
     *
     * @param string $front The URL path prefix for the front controller.
     * @return null
     */
    public function setFront($front)
    {
        $this->front = '/' . ltrim($front, '/');
    }

    /**
     * Sets the route value for the home page (URL path `/`).
     *
     * @param string $home_route The route value for the home page.
     * @return null
     */
    public function setHomeRoute($home_route)
    {
        $this->home_route = $home_route;
    }

    /**
     * Sets the route value for when there is no matching route.
     *
     * @param string $not_found_route The route value for when there is no
     * matching route.
     * @return null
     */
    public function setNotFoundRoute($not_found_route)
    {
        $this->not_found_route = $not_found_route;
    }

    /**
     * Sets the map of URL paths (keys) to file paths or class names (values).
     *
     * @param array $routes The map of URL paths (keys) to file paths or class
     * names (values).
     * @return null
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Given a URL path, returns a matching route value (either a file name or
     * a class name).
     *
     * @param string $path The URL path to be routed.
     * @return string A file path or a class name.
     */
    public function match($path)
    {
        $path = $this->fixPath($path);
        $route = $this->getRoute($path);
        return $this->fixRoute($route);
    }

    /**
     * Fixes the incoming URL path to strip the front controller script
     * name.
     *
     * @param string $path The incoming URL path.
     * @return string The fixed path.
     */
    protected function fixPath($path)
    {
        $len = strlen($this->front);

        if (substr($path, 0, $len) == $this->front) {
            $path = substr($path, $len);
        }

        return '/' . ltrim($path, '/');
    }

    /**
     * Returns the route value for a given URL path; uses the home route value
     * if the URL path is `/`.
     *
     * @param string $path The incoming URL path.
     * @return string The route value.
     */
    protected function getRoute($path)
    {
        if (isset($this->routes[$path])) {
            return $this->routes[$path];
        }

        if ($path == '/') {
            return $this->home_route;
        }

        return $path;
    }

    /**
     * Fixes a route specification to make sure it is found.
     *
     * @param string $route The matched route.
     * @return string The "fixed" route.
     * @throws RuntimeException when the route is a file but no pages directory
     * is specified.
     */
    protected function fixRoute($route)
    {
        if ($this->isFileRoute($route)) {
            return $this->fixFileRoute($route);
        }

        return $route;
    }

    /**
     * Is the matched route a file name?
     *
     * @param string $route The matched route.
     * @return bool
     */
    protected function isFileRoute($route)
    {
        return substr($route, 0, 1) == '/';
    }

    /**
     * Fixes a file route specification by finding the real path to see if it
     * exists in the pages directory and is readable.
     *
     * @param string $route The matched route.
     * @return string The real path if it exists, or the not-found route if it
     * does not.
     * @throws RuntimeException when the route is a file but no pages directory
     * is specified.
     */
    protected function fixFileRoute($route)
    {
        if (! $this->pages_dir) {
            throw new RuntimeException('No pages directory specified.');
        }

        $page = realpath($this->pages_dir . $route);

        if ($this->pageExists($page)) {
            return $page;
        }

        if ($this->isFileRoute($this->not_found_route)) {
            return $this->pages_dir . $this->not_found_route;
        }

        return $this->not_found_route;
    }

    /**
     * Does the pages directory have a matching readable file?
     *
     * @param string $file The file to check.
     * @return bool
     */
    protected function pageExists($file)
    {
        return $file != ''
            && substr($file, 0, strlen($this->pages_dir)) == $this->pages_dir
            && file_exists($file)
            && is_readable($file);
    }
}
