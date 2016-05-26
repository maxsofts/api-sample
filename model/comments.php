<?php

namespace max_api\model;

use max_api\database\query;

/**
 * Class comment
 * @package max_api\model
 */
class comments extends query
{
    /**
     * Lấy danh sách comment theo bài viết cùng các thông tin của user
     *
     * @param $parent_id
     * @param $limit
     * @param $offset
     * @param string $relate_type
     * @return bool|mixed
     * @throws \max_api\database\RuntimeException
     */
    public function getCommentsByRelateType($parent_id, $limit, $offset, $relate_type = "content")
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select(array(
                $query->quoteName("comment.id"),
                $query->quoteName("comment.user_id"),
                $query->quoteName("comment.comment"),
                $query->quoteName("comment.create_date"),
                $query->quoteName("comment.edit_date"),
                $query->quoteName("comment.relate_type"),
                $query->quoteName("comment.parent_id"),
            ))
            ->from(
                $query->quoteName("userinformation_usercomment", "comment")
            )
            //join user
            ->select(array(
                $query->quoteName("user.first_name", "user_first_name"),
                $query->quoteName("user.last_name", "user_last_name"),
            ))
            ->join("LEFT", "`auth_user` AS `user` ON `user`.`id` = `comment`.`user_id`")
            //join user profile
            ->select(array(
                $query->quoteName("profile.avatar_url", "user_avatar_url"),
            ))
            ->join("LEFT", "`userinformation_userprofile` AS `profile` ON `profile`.`user_id_id` = `comment`.`user_id`")
            ->where(array(
                $query->quoteName("comment.parent_id") . " = " . $query->quote($parent_id),
                $query->quoteName("comment.relate_type") . " = " . $query->quote($relate_type)
            ))
            ->order(
                $query->quoteName("comment.create_date") . " ASC"
            )
            ->setlimit($limit, $offset);

        $query->setQuery();

        $list = $query->loadObjects();

        if (!$list) {
            return false;
        }
        return $list;
    }

    /**
     * Đếm số comment hiện tại
     *
     * @param $parent_id
     * @param $relate_type
     * @return int
     */
    public function getCountComment($parent_id, $relate_type)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select(array(
                "COUNT(`id`)"
            ))
            ->from(
                $query->quoteName("userinformation_usercomment")
            )
            ->where(array(
                $query->quoteName("parent_id") . " = " . $query->quote($parent_id),
                $query->quoteName("relate_type") . " = " . $query->quote($relate_type),
            ));

        $query->setQuery();

        $count = $query->loadResult();

        if (!$count) {
            return 0;
        }

        return $count;
    }

    /**
     *
     * Cập nhật comment
     *
     * @param $parent_id
     * @param $user_id
     * @param $comment
     * @param $relate_type
     * @return bool
     */
    public function setComment($parent_id, $user_id, $comment, $relate_type = 'content')
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->insert(
                $query->quoteName("userinformation_usercomment")
            )
            ->set(array(
                $query->quoteName("parent_id") . " = " . $query->quote($parent_id),
                $query->quoteName("relate_type") . " = " . $query->quote($relate_type),
                $query->quoteName("user_id") . " = " . $query->quote($user_id),
                $query->quoteName("comment") . " = " . $query->quote($comment),
                $query->quoteName("create_date") . " = " . $query->quote(date('Y-m-d H:i:s')),
            ));

        $last_id = $query->setInsert();

        if (!$last_id) {
            return false;
        }

        if ($relate_type === 'content') {
            $count = $this->getCountComment($parent_id, $relate_type);

            $content = new contents();

            $update = $content->updateComment($parent_id, $count);

            if (!$update) {
                $this->_query->error_list = $content->_query->error_list;
            }
        }


        return $last_id;

    }

    /**
     *
     * Sửa comment
     *
     * @param $content_id
     * @param $user_id
     * @param $comment
     * @param string $relate_type
     * @return bool
     */
    public function updateComment($content_id, $user_id, $comment, $relate_type = 'content')
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("userinformation_usercomment")
            )
            ->set(array(
                $query->quoteName("comment") . " = " . $query->quote($comment),
                $query->quoteName("edit_date") . " = " . $query->quote(date('Y-m-d H:i:s')),
            ))
            ->where(array(
                $query->quoteName("content_id") . " = " . $query->quote($content_id),
                $query->quoteName("user_id") . " = " . $query->quote($user_id),
                $query->quoteName("relate_type") . " = " . $query->quote($relate_type),
            ));


        if (!$query->setUpdate()) {
            return false;
        }
        return true;
    }
}