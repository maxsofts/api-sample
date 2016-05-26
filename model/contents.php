<?php

namespace max_api\model;

use max_api\database\query;

/**
 * Class contents
 * @package max_api\model
 */
class contents extends query
{
    /**
     * @param $category_id
     * @param $limit
     * @param $offset
     * @return bool|mixed
     * @throws \max_api\database\RuntimeException
     */
    public function getContentsByCategory($category_id, $limit, $offset)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select("*")
            ->from(
                $query->quoteName("content_content", "content")
            )
            //join category
            ->select(array(
                $query->quoteName("category.title", "category_name"),
            ))
            ->join("LEFT", "`content_contentcategory` AS `category` ON `category`.`id` = `content`.`parent_id`")
            //join user create
            ->select(array(
                $query->quoteName("person_create.first_name", "person_create_first_name"),
                $query->quoteName("person_create.last_name", "person_create_last_name"),
            ))
            ->join("LEFT", "`auth_user` AS `person_create` ON `person_create`.`id` = `content`.`person_create_id`")
            ->where(
                $query->quoteName("content.parent_id") . " = " . $query->quote($category_id)
            )
            ->order(
                $query->quoteName("content.publicDate") . " DESC"
            )
            ->setLimit($limit, $offset);

        $query->setQuery();

        $list = $query->loadObjects();

        if (!$list) {
            return false;
        }

        return $list;
    }

    /**
     * @param $user_id
     * @param $limit
     * @param $offset
     * @return bool|mixed
     * @throws \max_api\database\RuntimeException
     */
    public function getContentsByUser($user_id, $limit, $offset)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select("*")
            ->from(
                $query->quoteName("content_content", "content")
            )
            ->where(
                $query->quoteName("content.person_create_id") . " = " . $query->quote($user_id)
            )
            //join category
            ->select(array(
                $query->quoteName("category.title", "category_name"),
            ))
            ->join("LEFT", "`content_contentcategory` AS `category` ON `category`.`id` = `content`.`parent_id`")
            //join user create
            ->select(array(
                $query->quoteName("person_create.first_name", "person_create_first_name"),
                $query->quoteName("person_create.last_name", "person_create_last_name"),
            ))
            ->join("LEFT", "`auth_user` AS `person_create` ON `person_create`.`id` = `content`.`person_create_id`")
            ->order(
                $query->quoteName("content.publicDate") . " DESC"
            )
            ->setLimit($limit, $offset);

        $query->setQuery();

        $list = $query->loadObjects();

        if (!$list) {
            return false;
        }

        return $list;
    }

    /**
     *
     * update Share
     *
     * @param $content_id
     * @param $count
     * @return bool
     */
    public function updateShare($content_id, $count)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("content_content")
            )
            ->set(
                $query->quoteName("share") . " = " . $query->quote($count)
            )
            ->where(
                $query->quoteName("id") . " = " . $query->quote($content_id)
            );

        $update = $query->setUpdate();

        if (!$update) {
            return false;
        }

        return true;
    }

    /**
     *
     * update Comment
     *
     * @param $content_id
     * @param $count
     * @return bool
     */
    public function updateComment($content_id, $count)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("content_content")
            )
            ->set(
                $query->quoteName("comment") . " = " . $query->quote($count)
            )
            ->where(
                $query->quoteName("id") . " = " . $query->quote($content_id)
            );

        $update = $query->setUpdate();

        if (!$update) {
            return false;
        }

        return true;
    }

    /**
     *
     * update Like
     *
     * @param $content_id
     * @param $count
     * @return bool
     */
    public function updateLike($content_id, $count)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("content_content")
            )
            ->set(
                $query->quoteName("like") . " = " . $query->quote($count)
            )
            ->where(
                $query->quoteName("id") . " = " . $query->quote($content_id)
            );

        $update = $query->setUpdate();

        if (!$update) {
            return false;
        }

        return true;
    }
}