<?php
namespace max_api\model;

use max_api\database\query;

class likes extends query
{
    /**
     *
     * Like
     *
     * @param $parent_id
     * @param $user_id
     * @param $relate_type
     * @return bool
     */
    public function like($parent_id, $user_id, $relate_type)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->insert(
                $query->quoteName("userinformation_userlike")
            )
            ->set([
                $query->quoteName("parent_id") . " = " . $query->quote($parent_id),
                $query->quoteName("user_id") . " = " . $query->quote($user_id),
                $query->quoteName("relate_type") . " = " . $query->quote($relate_type),
            ]);

        $last_id = $query->setInsert();

        if (!$last_id) {
            return false;
        }

        return $last_id;

    }

    /**
     *
     * Un Like
     *
     * @param $parent_id
     * @param $user_id
     * @param $relate_type
     * @return bool
     */
    public function unLike($parent_id, $user_id, $relate_type)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->delete(
                $query->quoteName("userinformation_userlike")
            )
            ->where([
                $query->quoteName("parent_id") . " = " . $query->quote($parent_id),
                $query->quoteName("user_id") . " = " . $query->quote($user_id),
                $query->quoteName("relate_type") . " = " . $query->quote($relate_type),
            ]);

        if (!$query->setQuery()) {
            return false;
        }
        return true;
    }

    /**
     * @param $parent_id
     * @param $user_id
     * @param $relate_type
     * @return bool
     */
    public function checkLike($parent_id, $user_id, $relate_type)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select(
                "COUNT(id)"
            )
            ->from(
                $query->quoteName("userinformation_userlike")
            )
            ->where([
                $query->quoteName("parent_id") . " = " . $query->quote($parent_id),
                $query->quoteName("user_id") . " = " . $query->quote($user_id),
                $query->quoteName("relate_type") . " = " . $query->quote($relate_type),
            ]);

        $query->setQuery();

        if (!$query->loadResult()) {
            return false;
        }

        return true;
    }

    /**
     *
     * Lấy số lượng like
     *
     * @param $parent_id
     * @param $relate_type
     * @return bool|mixed
     */
    public function getCountLike($parent_id, $relate_type)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select(
                "COUNT(id)"
            )
            ->from(
                $query->quoteName("userinformation_userlike")
            )
            ->where([
                $query->quoteName("parent_id") . " = " . $query->quote($parent_id),
                $query->quoteName("relate_type") . " = " . $query->quote($relate_type),
            ]);

        $query->setQuery();

        if (!$count = $query->loadResult()) {
            return 0;
        }

        return $count;
    }
}