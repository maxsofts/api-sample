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
        $query = $this->_query;

        $query->getQuery();

        $query->select(array('`token`'));

        $query->from("`max_api`");

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

        $query->from("`max_api`");

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

        $query->update("`max_api`");

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

        $query->from("`max_api`");

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
     *
     * Kiểm tra tài khoản và mật khẩu đẫ đúng chưa
     *
     * @param $username
     * @param $password
     * @return bool
     * @throws RuntimeException
     */
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


    /**
     *
     * Lấy thông tin tài khoản theo ID
     *
     * @param $userId
     * @return bool|mixed
     * @throws RuntimeException
     */
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


    /**
     *
     * Đăng ký tài khoản
     *
     * @param array $data
     * @return bool
     */
    public function register($data = array()){
        $query = $this->_query;

        $query->getQuery();

        $query->insert(
            $query->quoteName("auth_user")
        );

        $query->set(array(
            $query->quoteName("username")." = ".$query->quote($data["username"]),
            $query->quoteName("password")." = ".$query->quote(password::make_password($data["password"])),
            $query->quoteName("date_joined")." = ".$query->quote(date("Y-m-d H:i:s"))

        ));

        $lastid = $query->setInsert();

        if($query->db->errno){
            return false;
        }

        return $lastid;
    }

    /**
     *
     * Lưu trữ tài khoản facebook
     *
     * @param $data
     * @return bool
     */
    public function registerFaceBook($data){
        $query = $this->_query;

        $query->getQuery();

        $query->insert(
            $query->quoteName("auth_user")
        );

        $query->set(array(
            $query->quoteName("username")." = ".$query->quote($data->id),
            $query->quoteName("password")." = ".$query->quote(password::make_password(md5(uniqid(rand(), true)))),
            $query->quoteName("date_joined")." = ".$query->quote(date("Y-m-d H:i:s"))

        ));

        $last_user_id = $query->setInsert();

        if($query->db->errno){
            return false;
        }

        $query->getQuery();

        $query->insert(
            $query->quoteName("social_auth_usersocialauth")
        );

        $query->set(array(
            $query->quoteName("provider")." = ".$query->quote("facebook"),
            $query->quoteName("uid")." = ".$query->quote($data->id),
            $query->quoteName("extra_data")." = ".$query->quote(json_encode($data)),
            $query->quoteName("user_id")." = ".$query->quote($last_user_id)
        ));

        $check = $query->setInsert();

        if($query->db->errno || !$check){
            return false;
        }

        return $last_user_id;


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