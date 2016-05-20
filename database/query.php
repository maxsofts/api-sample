<?php

namespace max_api\database;
use max_api\contracts\password;

/**
 * Class database
 * @package max_api\database
 */
class query
{

    protected $_query;

    public function __construct()
    {
        $this->_query = new maxDatabase();
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
        $this->_query->getQuery();

        $this->_query->select(array('`token`'));

        $this->_query->from("`max_api`");

        $this->_query->where(array(
            "`vendor` = " . $this->_query->quote($vendor),
            "`hash` = " . $this->_query->quote($hash),
        ));

        $this->_query->setQuery();

        $token = $this->_query->loadResult();


        if (!$token) {
            $token = $this->setToken($vendor, $hash);
        }

        return $token;
    }

    public function checkToken($token)
    {
        /**
         * Set type select
         */
        $this->_query->getQuery();

        $this->_query->select(array('Count(`id`)'));

        $this->_query->from("`max_api`");

        $this->_query->where(array(
            "`token` = " . $this->_query->quote($token),
        ));

        $this->_query->setQuery();

        $check = $this->_query->loadResult();

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

        $this->_query->getQuery();

        $this->_query->update("`max_api`");

        $this->_query->set(
            "`token` = " . $this->_query->quote($token)
        );

        $this->_query->where(array(
            "`vendor` = " . $this->_query->quote($vendor),
            "`hash` = " . $this->_query->quote($hash),
        ));

        $update = $this->_query->setUpdate();

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

        $this->_query->getQuery();

        $this->_query->select(array('Count(`id`)'));

        $this->_query->from("`max_api`");

        $this->_query->where(array(
            "`vendor` = " . $this->_query->quote($vendor),
            "`hash` = " . $this->_query->quote($hash),
        ));

        $this->_query->setQuery();

        $count = $this->_query->loadResult();

        if ($count) {
            return true;
        }

        return false;
    }


    public function auth($username, $password)
    {
        $query = $this->_query;

        $query->getQuery();

        $query->select(array(
            $query->quoteName("user.password"),
            $query->quoteName("user.id"),
        ));

        $query->from(
            $query->quoteName("auth_user", "user")
        );

        $query->where(
            $query->quoteName("username")." = ".$query->quote($username)
        );

        $query->setQuery();

        $user = $query->loadObjects();


        if(!$user->password){
            return false;
        }

        if(!password::verify_Password($user->password,$password)){
            return false;
        }

        return $user->id;

    }

    public function getInfoUser($userId)
    {
        $query = $this->_query;

        $query->getQuery();

        $query->select(array(
            $query->quoteName("user.id"),
            $query->quoteName("user.username"),
            $query->quoteName("user.first_name"),
            $query->quoteName("user.last_name"),
            $query->quoteName("user.email"),
            $query->quoteName("user.date_joined"),
        ));

        $query->from(
            $query->quoteName("auth_user", "user")
        );

        $query->select(array(
            $query->quoteName("profile.point"),
            $query->quoteName("profile.count_content"),
            $query->quoteName("profile.avatar_url")
        ));

        $query->join("LEFT", "`userinformation_userprofile` AS `profile` ON `user`.id = `profile`.`user_id_id`");

        $query->where(
          $query->quoteName("user.id")." = ".$query->quote($userId)
        );

        $query->setQuery();

        if($query->db->errno){
            return false;
        }

        return $query->loadObjects();
    }
}