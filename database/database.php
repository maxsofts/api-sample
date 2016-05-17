<?php

namespace max_api\database;


/**
 * Class database
 * @package max_api\database
 */
class database
{
    /**
     * Config database
     */
    protected $_host = "localhost";
    protected $_dbname = "max_api_user";
    protected $_username = "root";
    protected $_password = "root";
    protected $_table_user = "max_api_user";
    protected $_connect;

    /**
     * database constructor.
     */
    public function __construct()
    {

        $this->dbConnect();
    }

    /**
     * Database connect
     */
    private function dbConnect()
    {
        $this->_connect = mysqli_connect($this->_host, $this->_username, $this->_password, $this->_dbname);
        // check connection
        if ($this->_connect->connect_error) {
            trigger_error('Database connection failed: ' . $this->_connect->connect_error, E_USER_ERROR);
        }
    }

    /**
     *
     * Database setQuery
     *
     * @param $query
     * @return bool
     */
    public function setQuery($query)
    {
        $results = $this->_connect->query($query);
        if ($results) {
            return $results;
        }

        return false;
    }

    /**
     *
     * Database test check auth
     *
     * @param $username
     * @param $password
     * @return bool
     */
    public function auth($username, $password)
    {
        if (!$username || !$password) {
            return false;
        }

        $query = "SELECT `id` FROM `$this->_table_user` WHERE `username` = \"$username\" AND `password` = \"$password\" ";

        $select = $this->setQuery($query);


        return $select;
    }

    /**
     *
     * Database set new token
     *
     * @param $id
     * @return bool|string
     */
    public function set_token($id)
    {
        $token = sha1(rand() . microtime());

        if (!$id) {
            return FALSE;
        }
        $query = "UPDATE `$this->_table_user` SET `token`=\"$token\" WHERE `id` = \"$id\"";


        try {
            /* switch autocommit status to FALSE. Actually, it starts transaction */
            $this->_connect->autocommit(FALSE);

            $res = $this->setQuery($query);

            if ($res === false) {
                return false;
            }

            $this->_connect->commit();

            $this->_connect->autocommit(TRUE);
            return $token;

        } catch (Exception $e) {

            $this->_connect->rollback();
            $this->_connect->autocommit(TRUE);

            return false;
        }

    }

    public function getField($table = "max_api_user", $columnIn = array(1 => 1), $columnOut = array('0'=>"*"))
    {

    }
}