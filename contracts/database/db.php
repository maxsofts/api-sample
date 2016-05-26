<?php

namespace max_api\contracts\database;

/**
 * Class db
 * @package max_api\contracts
 */
abstract class db implements dbImplement
{

    /**
     * @var    db  The database driver.
     * @since  11.1
     */
    protected $db = null;

    /**
     * @var    string  The SQL query (if a direct query string was provided).
     * @since  12.1
     */
    protected $sql = null;

    /**
     * @var    string  The query type.
     * @since  11.1
     */
    protected $type = '';

    /**
     * @var    db  The query element for a generic query (type = null).
     * @since  11.1
     */
    protected $element = null;

    /**
     * @var    db  The select element.
     */
    protected $select = null;

    /**
     * @var    db  The delete element.
     */
    protected $delete = null;

    /**
     * @var    db  The update element.
     */
    protected $update = null;

    /**
     * @var    db  The insert element.
     */
    protected $insert = null;

    /**
     * @var    db  The from element.
     */
    protected $from = null;

    /**
     * @var    db  The join element.
     */
    protected $join = null;

    /**
     * @var    db  The set element.
     */
    protected $set = null;

    /**
     * @var    db  The where element.
     */
    protected $where = null;

    /**
     * @var    db  The group by element.
     */
    protected $group = null;

    /**
     * @var    db  The having element.
     */
    protected $having = null;

    /**
     * @var    db  The column list for an INSERT statement.
     */
    protected $columns = null;

    /**
     * @var    db  The values list for an INSERT statement.
     */
    protected $values = null;

    /**
     * @var    db  The order element.
     */
    protected $order = null;

    /**
     * @var   object  The auto increment insert field element.
     */
    protected $autoIncrementField = null;

    /**
     * @var    db  The call element.
     */
    protected $call = null;

    /**
     * @var    db  The exec element.
     */
    protected $exec = null;

    /**
     * @var    db  The union element.
     */
    protected $union = null;

    /**
     * @var    db  The unionAll element.
     */
    protected $unionAll = null;

    /**
     * @var    db  The limit element.
     */
    protected $limit;

    /**
     * @var    db  The offset element.
     */
    protected $offset;

    /**
     * @var string the nameQuote
     */
    protected $nameQuote = '`';

    /**
     * @var data the element
     */
    protected $data;

    /**
     * @var db error MYSQL
     */
    public $error_list;


}