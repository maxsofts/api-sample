<?php

namespace max_api\model;

use max_api\database\query;

/**
 * Class categories
 * @package max_api\model
 */
class categories extends query
{

    /**
     * @return bool|mixed
     * @throws RuntimeException
     */
    public function getMenusMobile()
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select([
                $query->quoteName("category.id"),
                $query->quoteName("category.title"),
                $query->quoteName("category.class_icon"),
                $query->quoteName("category.parent_id"),
                $query->quoteName("category.level"),
                $query->quoteName("category.lft"),
                $query->quoteName("category.rght"),
                $query->quoteName("category.tree_id"),
                $query->quoteName("category.orderNumber"),
            ])
            ->from(
                $query->quoteName("content_contentcategory", "category")
            )
            ->where(
                $query->quoteName("category.is_menu_mobile") . " = 1"
            )
            ->order("orderNumber DESC");

        $query->setQuery();

        $list = $query->loadObjects();

        if (!$list) {
            return false;
        }

        return $list;
    }


    /**
     * @return bool|mixed
     * @throws \max_api\database\RuntimeException
     */
    public function getCategories()
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select([
                $query->quoteName("category.id"),
                $query->quoteName("category.title"),
                $query->quoteName("category.class_icon"),
                $query->quoteName("category.parent_id"),
                $query->quoteName("category.level"),
                $query->quoteName("category.lft"),
                $query->quoteName("category.rght"),
                $query->quoteName("category.tree_id"),
                $query->quoteName("category.orderNumber"),
            ])
            ->from(
                $query->quoteName("content_contentcategory", "category")
            )
            ->order("orderNumber ASC");

        $query->setQuery();

        $list = $query->loadObjects();

        if (!$list) {
            return false;
        }

        return $list;
    }


}