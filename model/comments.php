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
                $query->quoteName("comment.like_")
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
            //join media
            ->select(array(
                $query->quoteName("media.link", "media_link"),
            ))
            ->join("LEFT", "`content_mediacontent` AS `media` ON `media`.`parent_id` = `comment`.`id` AND `media`.`relate_type` = 'comment'")
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
        $count = $this->getCountComment($parent_id, $relate_type);

        if ($relate_type === 'content') {

            $content = new contents();

            $update = $content->updateCountComment($parent_id, $count);

            if (!$update) {
                $this->_query->error_list = $content->_query->error_list;
            }
        }
        if ($relate_type === 'comment') {
            $comment = new comments();

            $update = $comment->updateCountComment($parent_id, $count);

            if (!$update) {
                $this->_query->error_list = $comment->_query->error_list;
            }
        }

        if ($relate_type === 'status') {

            $status = new status();

            $update = $status->updateCountComment($parent_id, $count);

            if (!$update) {
                $this->_query->error_list = $status->_query->error_list;
            }
        }


        return $last_id;

    }

    /**
     * @param $id
     * @param $comment
     * @return bool
     */
    public function updateComment($id, $comment)
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
                $query->quoteName("id") . " = " . $query->quote($id)
            ));


        if (!$query->setUpdate()) {
            return false;
        }
        return true;
    }

    /**
     * @param $id
     * @return bool
     */
    public function deleteComment($id)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->delete("userinformation_usercomment")
            ->where(
                $query->quoteName('id') . " = " . $query->quote($id)
            );

        if (!$query->setQuery()) {
            return false;
        }
        //detete media
        $media = new media();

        if (!$media->deleteMedia($id, 'comment')) {
            $query->error_list = $media->_query->error_list;
        }

        return true;
    }

    /**
     *
     * Set Count Comment
     *
     * @param $id
     * @param $count
     * @return bool
     */
    public function updateCountComment($id, $count)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("userinformation_usercomment")
            )
            ->set(array(
                $query->quoteName("comment_count") . " = " . $query->quote($count),
            ))
            ->where(array(
                $query->quoteName("id") . " = " . $query->quote($id)
            ));


        if (!$query->setUpdate()) {
            return false;
        }
        return true;
    }

    /**
     *
     * Set Count Like
     *
     * @param $id
     * @param $count
     * @return bool
     */
    public function updateCountLike($id, $count)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("userinformation_usercomment")
            )
            ->set(array(
                $query->quoteName("like_count") . " = " . $query->quote($count),
            ))
            ->where(array(
                $query->quoteName("id") . " = " . $query->quote($id)
            ));


        if (!$query->setUpdate()) {
            return false;
        }
        return true;
    }

}