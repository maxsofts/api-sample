<?php
namespace max_api\model;

use max_api\database\query;

class status extends query
{

    /**
     * @param $user_id
     * @param string $text
     * @return bool
     */
    public function setStatus($user_id, $text = "")
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->insert(
                $query->quoteName("userinformation_userstatus")
            )
            ->set(array(
                $query->quoteName("create_date") . " = " . $query->quote(date('Y-m-d H:i:s')),
                $query->quoteName("text") . " = " . $query->quote($text),
                $query->quoteName("user_id_id") . " = " . $query->quote($user_id)
            ));

        return $query->setInsert();
    }

    /**
     * @param $id
     * @param string $text
     * @return bool
     */
    public function updateStatus($id, $text = "")
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("userinformation_userstatus")
            )
            ->set(array(
                $query->quoteName("edit_date") . " = " . $query->quote(date('Y-m-d H:i:s')),
                $query->quoteName("text") . " = " . $query->quote($text),
            ))
            ->where(
                $query->quoteName("id") . " = " . $query->quote($id)
            );

        return $query->setUpdate();
    }

    /**
     * @param $user_id
     * @param $limit
     * @param $offset
     * @return bool|mixed
     */
    public function getStatusByUser($user_id, $limit, $offset)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select([
                $query->quoteName("status.id"),
                $query->quoteName("status.text"),
                $query->quoteName("status.create_date"),
                $query->quoteName("status.edit_date"),
                $query->quoteName("status.comment_count"),
                $query->quoteName("status.like_count"),
                $query->quoteName("status.share_count"),

            ])
            ->from(
                $query->quoteName("userinformation_userstatus", "status")
            )
            ->select([
                $query->quoteName("media.link")
            ])
            ->join("LEFT", "`content_mediacontent` AS `media` ON `media`.`parent_id` = `status`.`id` AND `media`.`relate_type` = 'status'")
            ->where([
                $query->quoteName("user_id_id") . " = " . $query->quote($user_id)
            ])
            ->setLimit($limit, $offset);

        $query->setQuery();

        $list = $query->loadObjects();

        if (!$list) {
            return false;
        }

        return $list;
    }


    /**
     * @param $id
     * @return bool
     */
    public function deleteStatus($id)
    {

        $query = $this->_query;

        $query->getQuery();

        $query
            ->delete("userinformation_userstatus")
            ->where(
                $query->quoteName('id') . " = " . $query->quote($id)
            );

        if (!$query->setQuery()) {
            return false;
        }
        //detete media
        $media = new media();

        if (!$media->deleteMedia($id, 'status')) {
            $query->error_list[] = $media->_query->error_list;
        }

        $likes = new likes();

        if(!$likes->deleteLike($id,'status')){
            $query->error_list[] = $likes->_query->error_list;
        }

        //Lấy danh sách comment theo status -- sau đó delete nốt status
        $query->getQuery();

        $query
            ->select(
                $query->quoteName('id')
            )
            ->from(
                $query->quoteName('userinformation_usercomment')
            )
            ->where([
                $query->quoteName('parent_id') . ' = ' . $query->quote($id),
                $query->quoteName('relate_type') . ' = ' . $query->quote('status')
            ]);

        $query->setQuery();

        $listComment = $query->loadObjects();

        if ($listComment) {
            $modelComment = new comments();
            foreach ($listComment as $comment) {
                $modelComment->deleteComment($comment->id, 'status');
            }
        }

        return true;

    }


    /**
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
                $query->quoteName("userinformation_userstatus")
            )
            ->set(
                $query->quoteName("comment_count") . " = " . $query->quote($count)
            )
            ->where(
                $query->quoteName("id") . " = " . $query->quote($id)
            );

        $update = $query->setUpdate();

        if (!$update) {
            return false;
        }

        return true;
    }

    /**
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
                $query->quoteName("userinformation_userstatus")
            )
            ->set(
                $query->quoteName("like_count") . " = " . $query->quote($count)
            )
            ->where(
                $query->quoteName("id") . " = " . $query->quote($id)
            );

        $update = $query->setUpdate();

        if (!$update) {
            return false;
        }

        return true;
    }

    /**
     * @param $id
     * @param $count
     * @return bool
     */
    public function updateCountShare($id, $count)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("userinformation_userstatus")
            )
            ->set(
                $query->quoteName("share_count") . " = " . $query->quote($count)
            )
            ->where(
                $query->quoteName("id") . " = " . $query->quote($id)
            );

        $update = $query->setUpdate();

        if (!$update) {
            return false;
        }

        return true;
    }
}