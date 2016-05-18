<?php

namespace max_api\database;

/**
 * Class database
 * @package max_api\database
 */
class query
{

    protected $_query;

    public function __construct()
    {
        $this->_query = new maxDatabase();
    }

    public function apiCheck($vendor, $hash)
    {

        /**
         * Set type select
         */
        $this->_query->getQuery();

        $this->_query->__set("type", "select");

        $this->_query->select(array('`token`'));

        $this->_query->from("`max_api`");

        $this->_query->where(array(
            "`vendor` = " . $this->_query->quote($vendor),
            "`hash` = " . $this->_query->quote($hash),
        ));

        $this->_query->setQuery();

        $token = $this->_query->loadResult();


        if (!$token) {
            $token = $this->setToken($vendor, $hash);
        }

        return $token;
    }

    public function setToken($vendor, $hash)
    {
        $token = md5(uniqid(rand(), true));

        $this->_query->getQuery();

        $this->_query->update("`max_api`");

        $this->_query->set(
            "`token` = " . $this->_query->quote($token)
        );

        $this->_query->where(array(
            "`vendor` = " . $this->_query->quote($vendor),
            "`hash` = " . $this->_query->quote($hash),
        ));

        $update = $this->_query->setUpdate();

        if($update){
            return $token;
        }

        return false;

    }

}