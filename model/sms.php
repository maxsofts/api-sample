<?php
namespace max_api\model;

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
}