<?php

namespace max_api\model;

use max_api\database\query;

/**
 * Class comment
 * @package max_api\model
 */
class comment extends query
{
    /**
     *
     * Lấy danh sách comment theo bài viết cùng các thông tin của user
     *
     * @param $content_id
     * @param $limit
     * @param $offset
     * @return bool|mixed
     * @throws \max_api\database\RuntimeException
     */
    public function getCommentsByContent($content_id, $limit, $offset)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select(array(
                $query->quoteName("comment.id"),
                $query->quoteName("comment.user_id"),
                $query->quoteName("comment.comment"),
                $query->quoteName("comment.createDate"),
            ))
            ->from(
                $query->quoteName("content_commentcontent", "comment")
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
            ->where(
                $query->quoteName("comment.content_id") . " = " . $query->quote($content_id)
            )
            ->order(
                $query->quoteName("comment.createDate") . " ASC"
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
     *
     * Lấy tổng số comment
     *
     * @param $content_id
     * @return int|mixed
     * @throws \max_api\database\RuntimeException
     */
    public function getCountComment($content_id)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select(array(
                "COUNT(`id`)"
            ))
            ->from(
                $query->quoteName("content_commentcontent")
            )
            ->where(
                $query->quoteName("content_id") . " = " . $query->quote($content_id)
            );

        $query->setQuery();

        $count = $query->loadResult();

        if (!$count) {
            return 0;
        }

        return $count;
    }

    /**
     * Thêm mới comment
     *
     * @param $content_id
     * @param $user_id
     * @param $comment
     * @return bool
     */
    public function setComment($content_id, $user_id, $comment)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->insert(
                $query->quoteName("content_commentcontent")
            )
            ->set(array(
                $query->quoteName("content_id") . " = " . $query->quote($content_id),
                $query->quoteName("user_id") . " = " . $query->quote($user_id),
                $query->quoteName("comment") . " = " . $query->quote($comment),
                $query->quoteName("createDate") . " = " . $query->quote(date('Y-m-d H:i:s')),
            ));

        $last_id = $query->setInsert();

        if (!$last_id) {
            return false;
        }

        $count = $this->getCountComment($content_id);

        $content = new contents();

        $update = $content->updateComment($content_id, $count);

        if (!$update) {
            $this->_query->error_list = $content->_query->error_list;
        }

        return $last_id;

    }
}