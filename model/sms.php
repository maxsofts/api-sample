<?php
namespace max_api\model;

use max_api\contracts\config;
use max_api\database\query;

class sms extends query
{
    public function insertSms($data)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->insert(
                $query->quoteName("api_sendsms")
            )
            ->set([
                $query->quoteName("uuid") . " = " . $query->quote($data['id']),
                $query->quoteName("status") . " = " . $query->quote($data['status']),
                $query->quoteName("phone") . " = " . $query->quote($data['phone']),
                $query->quoteName("create_date") . " = " . $query->quote(date('Y-m-d H:i:s'))
            ]);

        return $query->setInsert();
    }

    /**
     * @param $phone
     * @return bool
     */
    public function checkLimitTime($phone)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select(
                $query->quoteName("create_date")
            )
            ->from(
                $query->quoteName("api_sendsms")
            )
            ->where([
                $query->quoteName("phone") . " = " . $query->quote($phone),
                $query->quoteName("status") . " = " . $query->quote(0),
            ])
            ->order("create_date DESC");

        $query->setQuery();


        $date = $query->loadResult();

        if (!$date) {
            return true;
        }

        $limitTime = config::get("sms.limit_time");

        return strtotime("now") > strtotime("$date +$limitTime minutes") ? true : false;
    }

}