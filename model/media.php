<?php
namespace max_api\model;

use max_api\contracts\sftp;
use max_api\contracts\uploadHelper;
use max_api\database\query;

class media extends query
{
    /**
     * @param $user_id
     * @param $link
     * @param int $media_type
     * @param string $relate_type
     * @return bool
     */
    public function setMedia($user_id, $link, $media_type = 0, $relate_type = "content")
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->insert(
                $query->quoteName("content_mediacontent")
            )
            ->set(array(
                $query->quoteName("user_id") . " = " . $query->quote($user_id),
                $query->quoteName("link") . " = " . $query->quote($link),
                $query->quoteName("media_type") . " = " . $query->quote($media_type),
                $query->quoteName("relate_type") . " = " . $query->quote($relate_type),
                $query->quoteName("create_date") . " = " . $query->quote(date('Y-m-d H:i:s')),
            ));

        $last_id = $query->setInsert();

        if (!$last_id) {
            return false;
        }
        return $last_id;
    }

    /**
     *
     * Update parent_id
     *
     * @param $id
     * @param $parent_id
     * @return bool
     */
    public function updateParent($id, $parent_id)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->update(
                $query->quoteName("content_mediacontent")
            )
            ->set(
                $query->quoteName("parent_id") . " = " . $query->quote($parent_id)
            )
            ->where(
                $query->quoteName("id") . " = " . $query->quote($id)
            );

        if (!$query->setUpdate()) {
            return false;
        }

        return true;
    }

    /**
     * @param $parent_id
     * @param string $relate_type
     * @return bool
     */
    public function deleteMedia($parent_id, $relate_type = "content")
    {
        $query = $this->_query;

        $query->getQuery();

        //get link unset
        $query
            ->select(
                $query->quoteName("link")
            )
            ->from(
                $query->quoteName("content_mediacontent")
            )
            ->where(array(
                $query->quoteName("parent_id") . " = " . $query->quote($parent_id),
                $query->quoteName("relate_type") . " = " . $query->quote($relate_type),
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

        $query
            ->delete(
                $query->quoteName("content_mediacontent")
            )
            ->where(array(
                $query->quoteName("parent_id") . " = " . $query->quote($parent_id),
                $query->quoteName("relate_type") . " = " . $query->quote($relate_type),
            ));

        if (!$query->setQuery()) {
            return false;
        }
        return true;
    }

    /**
     * @param $id
     * @return bool
     */
    public function deleteMediaById($id)
    {
        $query = $this->_query;

        $query->getQuery();

        //get link unset
        $query
            ->select(
                $query->quoteName("link")
            )
            ->from(
                $query->quoteName("content_mediacontent")
            )
            ->where(array(
                $query->quoteName("id") . " = " . $query->quote($id)
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

        $query
            ->delete(
                $query->quoteName("content_mediacontent")
            )
            ->where(array(
                $query->quoteName("id") . " = " . $query->quote($id),
            ));

        if (!$query->setQuery()) {
            return false;
        }
        return true;
    }
}