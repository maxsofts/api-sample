<?php

namespace max_api\model;

use max_api\database\query;
use max_api\contracts\password;

/**
 * Class users
 * @package max_api\model
 */
class users extends query
{
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
            $query->quoteName("username") . " = " . $query->quote($username)
        );

        $query->setQuery();

        $user = $query->loadObject();


        if (!$user->password) {
            return false;
        }

        if (!password::verify_Password($user->password, $password)) {
            return false;
        }

        //update last login
        $query->getQuery();

        $query->update(
            $query->quoteName('auth_user')
        );

        $query->set(array(
            $query->quoteName('last_login') . " = " . $query->quote(date("Y-m-d H:i:s"))
        ));

        $query->where(
            $query->quoteName("id") . " = " . $query->quote($user->id)
        );

        $query->setUpdate();

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
            $query->quoteName("user.id") . " = " . $query->quote($userId)
        );

        $query->setQuery();

        if ($query->db->errno) {
            return false;
        }

        return $query->loadObject();
    }

    /**
     *
     * Đăng ký tài khoản
     *
     * @param array $data
     * @return bool
     */
    public function register($data = array())
    {
        $query = $this->_query;

        $query->getQuery();

        $query->insert(
            $query->quoteName("auth_user")
        );

        $query->set(array(
            $query->quoteName("username") . " = " . $query->quote($data["username"]),
            $query->quoteName("first_name") . " = " . $query->quote($data["first_name"]),
            $query->quoteName("password") . " = " . $query->quote(password::make_password($data["password"])),
            $query->quoteName("date_joined") . " = " . $query->quote(date("Y-m-d H:i:s"))

        ));

        $last_user_id = $query->setInsert();

        /**
         * Thiếu phần tiến hành số điện thoại kích hoạt
         */

        /*
         * Insert profile with user
         */
        $query->getQuery();

        $query->insert(
            $query->quoteName("userinformation_userprofile")
        );

        $query->set(array(
            $query->quoteName("user_id_id") . " = " . $query->quote($last_user_id),
        ));

        $query->setInsert();

        if ($query->db->errno) {
            return false;
        }


        return $last_user_id;
    }

    /**
     *
     * Lưu trữ tài khoản facebook
     *
     * @param $data
     * @return bool
     */
    public function registerFaceBook($name, $data)
    {
        $query = $this->_query;

        $query->getQuery();

        $query->insert(
            $query->quoteName("auth_user")
        );

        $query->set(array(
            $query->quoteName("username") . " = " . $query->quote($data->id),
            $query->quoteName("first_name") . " = " . $query->quote($name),
            $query->quoteName("password") . " = " . $query->quote(password::make_password(md5(uniqid(rand(), true)))),
            $query->quoteName("date_joined") . " = " . $query->quote(date("Y-m-d H:i:s")),
            $query->quoteName("is_active") . " = " . $query->quote("1"),
        ));

        $last_user_id = $query->setInsert();

        if ($query->db->errno || !$last_user_id) {
            return false;
        }

        $query->getQuery();

        $query->insert(
            $query->quoteName("social_auth_usersocialauth")
        );

        $query->set(array(
            $query->quoteName("provider") . " = " . $query->quote("facebook"),
            $query->quoteName("uid") . " = " . $query->quote($data->id),
            $query->quoteName("extra_data") . " = " . $query->quote(json_encode($data)),
            $query->quoteName("user_id") . " = " . $query->quote($last_user_id)
        ));


        $check = $query->setInsert();

        if ($query->db->errno || !$check) {
            return false;
        }

        /*
        * Insert profile with user
        */
        $query->getQuery();

        $query->insert(
            $query->quoteName("userinformation_userprofile")
        );

        $query->set(array(
            $query->quoteName("user_id_id") . " = " . $query->quote($last_user_id),
        ));

        $query->setInsert();

        if ($query->db->errno) {
            return false;
        }

        return $last_user_id;
    }

    /**
     *
     * Get User Id by facebook id
     *
     * @param $fb_id
     * @return bool
     * @throws RuntimeException
     */
    public function getUserIdByFB($fb_id)
    {
        $query = $this->_query;

        $query->getQuery();

        $query->select(array(
            $query->quoteName("social.user_id"),
        ));

        $query->from(
            $query->quoteName("social_auth_usersocialauth", "social")
        );

        $query->where(array(
            $query->quoteName("social.uid") . " = " . $query->quote($fb_id)
        ));

        $query->setQuery();

        $user_id = $query->loadResult();

        if ($user_id) {
            return $user_id;
        }

        return false;
    }


    /**
     * @param $user_id
     * @param $password
     * @return bool
     * @throws RuntimeException
     */
    public function checkPassById($user_id, $password)
    {
        $query = $this->_query;

        $query->getQuery();

        $query->select(array(
            $query->quoteName("user.password"),
        ));

        $query->from(
            $query->quoteName("auth_user", "user")
        );

        $query->where(
            $query->quoteName("id") . " = " . $query->quote($user_id)
        );

        $query->setQuery();

        $oldPass = $query->loadResult();


        if (!$oldPass) {
            return false;
        }

        if (!password::verify_Password($oldPass, $password)) {
            return false;
        }

        return true;
    }


    /**
     *
     * Thay đổi mật khẩu
     *
     * @param $user_id
     * @param $password
     * @return bool
     */
    public function changePass($user_id, $password)
    {
        $query = $this->_query;

        $query->getQuery();

        $query->update(
            $query->quoteName("auth_user")
        );

        $query->set(
            $query->quoteName("password") . " = " . $query->quote(password::make_password($password))
        );

        $query->where(array(
            $query->quoteName("id") . " = " . $query->quote($user_id),
        ));

        $update = $query->setUpdate();

        if (!$update) {
            return false;
        }

        return true;
    }

    /**
     *
     * Update Avatar
     *
     * @param $user_id
     * @param $path
     * @return bool
     */
    public function updateAvatar($user_id, $path)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("userinformation_userprofile")
            )
            ->set(
                $query->quoteName("avatar_url") . " = " . $query->quote($path)
            )
            ->where(array(
                $query->quoteName("user_id_id") . " = " . $query->quote($user_id),
            ));

        $update = $query->setUpdate();

        if (!$update) {
            return false;
        }

        return true;
    }

    /**
     *
     * Cập nhật thông tin cá nhân
     *
     * @param $user_id
     * @param $data
     * @return bool
     */
    public function updateProfile($user_id, $data)
    {
        $query = $this->_query;

        $query->getQuery();

        $query->update(
            $query->quoteName("auth_user")
        );

        $query->set(array(
                $query->quoteName("email") . " = " . $query->quote($data['email']),
                $query->quoteName("first_name") . " = " . $query->quote($data['first_name'])
            )
        );

        $query->where(array(
            $query->quoteName("id") . " = " . $query->quote($user_id),
        ));

        $update = $query->setUpdate();

        if (!$update) {
            return false;
        }

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("userinformation_userprofile")
            )
            ->set(
                $query->quoteName("phone") . " = " . $query->quote($data['phone'])
            )
            ->where(array(
                $query->quoteName("user_id_id") . " = " . $query->quote($user_id),
            ));

        $update = $query->setUpdate();

        if (!$update) {
            return false;
        }

        return true;
    }


    /**
     *
     * Lấy thông tin danh sách user
     *
     * @param $limit
     * @param $offset
     * @param $order
     * @return bool|mixed
     * @throws RuntimeException
     */
    public function getUsers($limit, $offset, $order)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select(array(
                $query->quoteName("profile.phone"),
                $query->quoteName("profile.point"),
                $query->quoteName("profile.count_content"),
                $query->quoteName("profile.avatar_url")
            ))
            ->from(
                $query->quoteName("userinformation_userprofile", "profile")
            )
            //Join User auth
            ->select(array(
                $query->quoteName("user.first_name"),
                $query->quoteName("user.last_name"),
                $query->quoteName("user.email")
            ))
            ->join("LEFT", "`auth_user` AS `user` ON `user`.`id` =  `profile`.`user_id_id`")
            //Join honour
            ->select(array(
                $query->quoteName("hounour.name", "hounour_name"),
            ))
            ->join("LEFT", "`userinformation_honourable_name` AS `hounour` ON `hounour`.`id` =  `profile`.`honour_id`")
            ->order($order)
            ->setLimit($limit, $offset);

        $query->setQuery();


        $list = $query->loadObjects();

        if (!$list) {
            return false;
        }

        return $list;
    }
}