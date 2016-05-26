<?php

namespace max_api\database;


/**
 * Class database
 * @package api_userapi\database
 */
class query
{

    protected $_query;

    public function __construct()
    {

        if (is_null($this->_query)) {
            $this->_query = new maxDatabase();
        }

    }

    public function __destruct()
    {
        $this->_query = null;
    }


    /**
     *
     * Lấy token
     *
     * @param $vendor
     * @param $hash
     * @return bool|string
     * @throws RuntimeException
     */
    public function apiCheck($vendor, $hash)
    {
        /**
         * Set type select
         */
        $query = $this->_query;

        $query->getQuery();

        $query->select(array('`token`'));

        $query->from("`api_userapi`");

        $query->where(array(
            "`vendor` = " . $this->_query->quote($vendor),
            "`hash` = " . $this->_query->quote($hash),
        ));

        $query->setQuery();

        $token = $this->_query->loadResult();


        if (!$token) {
            $token = $this->setToken($vendor, $hash);
        }

        return $token;
    }

    /**
     *
     * Kiểm tra token có tồn tài hay không
     *
     * @param $token
     * @return bool
     * @throws RuntimeException
     */
    public function checkToken($token)
    {
        /**
         * Set type select
         */
        $query = $this->_query;

        $query->getQuery();

        $query->select(array('Count(`id`)'));

        $query->from("`api_userapi`");

        $query->where(array(
            "`token` = " . $query->quote($token),
        ));

        $query->setQuery();

        $check = $query->loadResult();

        if ($check) {
            return true;
        }

        return false;

    }

    /**
     *
     * Tạo token mới
     *
     * @param $vendor
     * @param $hash
     * @return bool|string
     */
    public function setToken($vendor, $hash)
    {
        $token = md5(uniqid(rand(), true));

        $query = $this->_query;

        $query->getQuery();

        $query->update("`api_userapi`");

        $query->set(
            "`token` = " . $query->quote($token)
        );

        $query->where(array(
            "`vendor` = " . $query->quote($vendor),
            "`hash` = " . $query->quote($hash),
        ));

        $update = $query->setUpdate();

        if ($update) {
            return $token;
        }

        return false;
    }

    /**
     *
     * Kiểm tra xem vendor và hash có tồn tại không
     *
     * @param $vendor
     * @param $hash
     * @return bool
     * @throws RuntimeException
     */
    public function checkExitsVendor($vendor, $hash)
    {
        $query = $this->_query;

        $query->getQuery();

        $query->select(array('Count(`id`)'));

        $query->from("`api_userapi`");

        $query->where(array(
            "`vendor` = " . $query->quote($vendor),
            "`hash` = " . $query->quote($hash),
        ));

        $query->setQuery();

        $count = $query->loadResult();

        if ($count) {
            return true;
        }

        return false;
    }

    /**
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }
}