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
 * A lazy-connecting proxy class for `mysql_*()` functions.
 *
 * Usage:
 *
 *     <?php
 *     // creates a MysqlDatabase instance, but does not connect
 *     $db = new MysqlDatabase('host', 'user', 'pass');
 *
 *     // connects to the server and selects a database on that connection
 *     $db->selectDb('my_database'); // or $db->select_db('my_database');
 *
 *     // issue a query and get back a result resource
 *     $result = $db->query('SELECT * FROM table_name');
 *     ?>
 *
 * @see http://php.net/manual/en/ref.mysql.php
 * @package mlaphp/mlaphp
 */
class MysqlDatabase
{
    /**
     * @var string The server to connect to.
     */
    protected $server;

    /**
     * @var string Connect with this username.
     */
    protected $username;

    /**
     * @var string Connect with this password.
     */
    protected $password;

    /**
     * @var bool Create a new link resource?
     */
    protected $new_link = false;

    /**
     * @var int Use these connection flags.
     */
    protected $client_flags = 0;

    /**
     * @var resource The MySQL link identifier.
     */
    protected $link;

    /**
     * @var array MySQL functions that take a link identifier argument, mapped
     * to the argument number for the link identifier in that function.
     */
    protected $link_arg = array(
        'mysql_affected_rows'       => 1,
        'mysql_client_encoding'     => 1,
        'mysql_close'               => 1,
        'mysql_create_db'           => 2,
        'mysql_db_query'            => 3,
        'mysql_drop_db'             => 2,
        'mysql_errno'               => 1,
        'mysql_error'               => 1,
        'mysql_get_host_info'       => 1,
        'mysql_get_proto_info'      => 1,
        'mysql_get_server_info'     => 1,
        'mysql_info'                => 1,
        'mysql_insert_id'           => 1,
        'mysql_list_dbs'            => 1,
        'mysql_list_fields'         => 3,
        'mysql_list_processes'      => 1,
        'mysql_list_tables'         => 2,
        'mysql_ping'                => 1,
        'mysql_query'               => 2,
        'mysql_real_escape_string'  => 2,
        'mysql_select_db'           => 2,
        'mysql_set_charset'         => 2,
        'mysql_stat'                => 1,
        'mysql_thread_id'           => 1,
        'mysql_unbuffered_query'    => 2,
    );

    /**
     * Constructor.
     *
     * @param string $server The server to connect to.
     * @param string $username Connect with this username.
     * @param string $password Connect with this password.
     * @param bool $new_link Create a new link resource?
     * @param int $client_flags Use these connection flags.
     * @see http://php.net/manual/en/function.mysql-connect.php
     */
    public function __construct(
        $server = null,
        $username = null,
        $password = null,
        $new_link = false,
        $client_flags = 0
    ) {
        if (! $server) {
            $server = ini_get('mysql.default_host');
        }

        if (! $username) {
            $username = ini_get('mysql.default_user');
        }

        if (! $password) {
            $password = ini_get('mysql.default_password');
        }

        $this->server = $server;
        $this->username = $username;
        $this->password = $password;
        $this->new_link = $new_link;
        $this->client_flags = $client_flags;
    }

    /**
     * Proxies method calls to mysql_*() functions. Lazily connects to the
     * database for functions that require a link identifier. Explicitly adds
     * the optional link identifier argument if needed.
     *
     * @param string $func The called method name.
     * @param array $args Arguments passed to the method.
     * @return mixed The result of the function call.
     */
    public function __call($func, $args = array())
    {
        $func = $this->getFunctionName($func);
        $this->lazyConnect($func);
        $args = $this->addLinkArgument($func, $args);
        return call_user_func_array($func, $args);
    }

    /**
     * Closes the MySQL connection and clears the $link property.
     *
     * @return mixed True if the close succeeded, false if not, null if there
     * was no link to close.
     */
    public function close()
    {
        if (! $this->link) {
            return;
        }

        $result = mysql_close($this->link);
        if ($result) {
            $this->link = null;
        }

        return $result;
    }

    /**
     * Converts a `camelCase` magic method name to a snake-case MySQL function
     * name; `snake_case` method names remain the same.
     *
     * @param string $func The magic method name as called.
     * @return string
     */
    protected function getFunctionName($func)
    {
        // selectDb() -> select_Db() -> msyql_select_db()
        $func = preg_replace('/([a-z])([A-Z])/', '$1_$2' , $func);
        return 'mysql_' . strtolower($func);
    }

    /**
     * Given a MySQL function name and an array of arguments, adds the link
     * identifier to the end of the arguments if one is needed.
     *
     * @param string $func The MySQL function name.
     * @param array $args The arguments to the function.
     * @return array The arguments, with the link identifier if one is needed.
     */
    protected function addLinkArgument($func, $args)
    {
        if (! isset($this->link_arg[$func])) {
            return $args;
        }

        if (count($args) >= $this->link_arg[$func]) {
            return $args;
        }

        $args[] = $this->link;
        return $args;
    }

    /**
     * Given a MySQL function name, connects to the server if there is no link
     * and identifier and one is needed.
     *
     * @param string $func The MySQL function name.
     * @return null
     * @throws RuntimeException if the connection attempt fails.
     */
    protected function lazyConnect($func)
    {
        // do not reconnect
        if ($this->link) {
            return;
        }

        // connect only for functions that need a link
        if (! isset($this->link_arg[$func])) {
            return;
        }

        // connect and retain the identifier
        $this->link = mysql_connect(
            $this->server,
            $this->username,
            $this->password,
            $this->new_link,
            $this->client_flags
        );

        // throw exception on error
        if (! $this->link) {
            throw new RuntimeException(
                mysql_error(),
                mysql_errno()
            );
        }
    }
}
