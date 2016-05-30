<?php

namespace max_api\model;

use max_api\database\query;

/**
 * Class shares
 * @package max_api\model
 */
class shares extends query
{

    /**
     * @param $user_id
     * @param $parent_id
     * @param string $relate_type
     * @return bool
     */
    public function setShare($user_id, $parent_id, $relate_type = "content")
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->insert(
                $query->quoteName("content_sharecontent")
            )
            ->set(array(
                $query->quoteName("parent_id") . " = " . $query->quote($parent_id),
                $query->quoteName("relate_type") . " = " . $query->quote($relate_type),
                $query->quoteName("user_id") . " = " . $query->quote($user_id),
                $query->quoteName("create_date") . " = " . $query->quote(date('Y-m-d H:i:s')),
            ));

        $last_id = $query->setInsert();

        if (!$last_id) {
            return false;
        }
        $count = $this->getCountShare($parent_id, $relate_type);

        if ($relate_type === 'content') {

            $content = new contents();

            $update = $content->updateCountShare($parent_id, $count);

            if (!$update) {
                $this->_query->error_list = $content->_query->error_list;
            }
        }

        if ($relate_type === 'status') {

            $status = new status();

            $update = $status->updateCountShare($parent_id, $count);

            if (!$update) {
                $this->_query->error_list = $status->_query->error_list;
            }
        }


        return $last_id;
    }

    /**
     * @param $parent_id
     * @param $relate_type
     * @return int|mixed
     */
    public function getCountShare($parent_id, $relate_type)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select(
                "COUNT(id)"
            )
            ->from(
                $query->quoteName("content_sharecontent")
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