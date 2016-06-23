<?php

namespace max_api\model;

use max_api\contracts\random;
use max_api\contracts\sftp;
use max_api\contracts\uploadHelper;
use max_api\database\query;
use max_api\contracts\password;

/**
 * Class users
 * @package max_api\model
 */
class users extends query
{
    /**
     * Kiểm tra tài khoản và mật khẩu đẫ đúng chưa
     *
     * @param $username
     * @param $password
     * @return bool
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
            $query->quoteName("is_active")
        ));

        $query->from(
            $query->quoteName("auth_user", "user")
        );

        $query->select(array(
            $query->quoteName("profile.point"),
            $query->quoteName("profile.count_content"),
            $query->quoteName("profile.avatar_url"),
            $query->quoteName("profile.confirm_code"),
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

        /*
         * Insert profile with user
         */
        $query->getQuery();

        $query->insert(
            $query->quoteName("userinformation_userprofile")
        );

        $query->set(array(
            $query->quoteName("user_id_id") . " = " . $query->quote($last_user_id),
            $query->quoteName("phone") . " = " . $query->quote($data["username"]),
            $query->quoteName("confirm_code") . " = " . $query->quote($data["confirm_code"]),
        ));

        $query->setInsert();

        if ($query->db->errno) {
            return false;
        }
        /**
         * Insert thành công
         */
        $data = [
            "id" => $last_user_id,
            "is_active" => 0,
            "confirm_code" => $data["confirm_code"]
        ];

        return $data;
    }

    /**
     * Lưu trữ tài khoản facebook
     * @param $name
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
     * @param $user_id
     * @return bool|string
     */
    public function update_confirm_code($user_id)
    {

        $confirm_code = random::render();

        $query = $this->_query;

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("userinformation_userprofile")
            )
            ->set(
                $query->quoteName("confirm_code") . " = " . $query->quote($confirm_code)
            )
            ->where(array(
                $query->quoteName("user_id_id") . " = " . $query->quote($user_id),
            ));

        if (!$query->setUpdate()) {
            return false;
        }

        return $confirm_code;
    }

    /**
     *
     * Get User Id by facebook id
     *
     * @param $fb_id
     * @return bool
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
     *
     * Kiểm tra password theo User ID
     *
     * @param $user_id
     * @param $password
     * @return bool
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
        //Xoá ảnh cũ
        $query = $this->_query;

        $query->getQuery();

        //get link unset
        $query
            ->select(
                $query->quoteName("avatar_url")
            )
            ->from(
                $query->quoteName("userinformation_userprofile")
            )
            ->where(array(
                $query->quoteName("user_id_id") . " = " . $query->quote($user_id)
            ));

        $query->setQuery();

        $imageLink = $query->loadResult();


        if ($imageLink) {
            $imageDir = uploadHelper::linkToDir($imageLink);

            $sftp = new sftp();

            if (!$sftp) {
                $this->_query->error_list = [
                    "message" => "can't delete image on host"
                ];
            }
            if ($sftp->is_file($imageDir)) {
                if (!$sftp->delete($imageDir)) {
                    $this->_query->error_list = [
                        "message" => "can't delete image on host"
                    ];
                    return false;
                }
            }
        }

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

//        $query->getQuery();
//
//        $query
//            ->update(
//                $query->quoteName("userinformation_userprofile")
//            )
//            ->set(
//                $query->quoteName("phone") . " = " . $query->quote($data['phone'])
//            )
//            ->where(array(
//                $query->quoteName("user_id_id") . " = " . $query->quote($user_id),
//            ));
//
//        $update = $query->setUpdate();
//
//        if (!$update) {
//            return false;
//        }

        return true;
    }

    /**
     * @param $id
     * @param $confirm_code
     * @param $phone
     * @return bool
     */
    public function updateTmpPhone($id, $confirm_code, $phone)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("userinformation_userprofile")
            )
            ->set([
                $query->quoteName("tmp_phone") . " = " . $query->quote($phone),
                $query->quoteName("tmp_phone_code") . " = " . $query->quote($confirm_code),
            ])
            ->where(array(
                $query->quoteName("user_id_id") . " = " . $query->quote($id),
            ));

        var_dump($query);die();

       return $query->setUpdate();
    }

    /**
     * @param $id
     * @param $phone
     * @return bool
     */
    public function updateNewPhone($id, $phone)
    {
        $query = $this->_query;

        $query->getQuery();

        $query->update(
            $query->quoteName("auth_user")
        );

        $query->set(array(
                $query->quoteName("username") . " = " . $query->quote($phone),
            )
        );

        $query->where(array(
            $query->quoteName("id") . " = " . $query->quote($id),
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
                $query->quoteName("phone") . " = " . $query->quote($phone)
            )
            ->where(array(
                $query->quoteName("user_id_id") . " = " . $query->quote($id),
            ));

        $query->setUpdate();

        return true;
    }

    /**
     * @param $id
     * @param $code
     * @param $phone
     * @return mixed
     */
    public function checkNewPhone($id, $code, $phone)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select("COUNT(`id`)")
            ->from(
                $query->quoteName("userinformation_userprofile")
            )
            ->where([
                $query->quoteName("user_id_id") . " = " . $query->quote($id),
                $query->quoteName("tmp_phone") . " = " . $query->quote($phone),
                $query->quoteName("tmp_phone_code") . " = " . $query->quote($code)
            ]);

        $query->setQuery();


        return $query->loadResult();
    }

    /**
     * @param $id
     * @return bool
     */
    public function active($id)
    {
        $query = $this->_query;

        $query->getQuery();


        $query
            ->update(
                $query->quoteName("auth_user")
            )
            ->set(
                $query->quoteName("is_active") . " = " . $query->quote(1)
            )
            ->where(array(
                $query->quoteName("id") . " = " . $query->quote($id),
            ));


        return $query->setUpdate();
    }

    /**
     *
     * Lấy thông tin danh sách user
     *
     * @param $limit
     * @param $offset
     * @param $order
     * @return bool|mixed
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

    /**
     * @param $username
     * @return bool|mixed
     */
    public function getIdByUsername($username)
    {
        $query = $this->_query;

        $query->getQuery();

        $query->select(array(
            $query->quoteName("user.id"),
        ));

        $query->from(
            $query->quoteName("auth_user", "user")
        );

        $query->where(
            $query->quoteName("username") . " = " . $query->quote($username)
        );

        $query->setQuery();

        $id = $query->loadResult();


        if (!$id) {
            return false;
        }
        return $id;
    }

    /**
     * @param $username
     * @return mixed
     */
    public function getAllByUserName($username)
    {
        $query = $this->_query;

        $query->getQuery();

        $query->select("*");

        $query->from(
            $query->quoteName("auth_user", "user")
        );

        $query->where(
            $query->quoteName("username") . " = " . $query->quote($username)
        );

        $query->setQuery();

        return $query->loadObject();
    }


    public function deleteUserByUsername($username)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->delete(
                $query->quoteName("auth_user")
            )
            ->where(
                $query->quoteName("username") . " = " . $query->quote($username)
            );

        if (!$query->setQuery()) {
            return false;
        }

        return true;

    }
}