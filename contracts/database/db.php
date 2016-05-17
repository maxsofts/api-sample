<?php

namespace max_api\contracts\database;

/**
 * Class db
 * @package max_api\contracts
 */
abstract class db
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
     * @since  11.1
     */
    protected $select = null;

    /**
     * @var    db  The delete element.
     * @since  11.1
     */
    protected $delete = null;

    /**
     * @var    db  The update element.
     * @since  11.1
     */
    protected $update = null;

    /**
     * @var    db  The insert element.
     * @since  11.1
     */
    protected $insert = null;

    /**
     * @var    db  The from element.
     * @since  11.1
     */
    protected $from = null;

    /**
     * @var    db  The join element.
     * @since  11.1
     */
    protected $join = null;

    /**
     * @var    db  The set element.
     * @since  11.1
     */
    protected $set = null;

    /**
     * @var    db  The where element.
     * @since  11.1
     */
    protected $where = null;

    /**
     * @var    db  The group by element.
     * @since  11.1
     */
    protected $group = null;

    /**
     * @var    db  The having element.
     * @since  11.1
     */
    protected $having = null;

    /**
     * @var    db  The column list for an INSERT statement.
     * @since  11.1
     */
    protected $columns = null;

    /**
     * @var    db  The values list for an INSERT statement.
     * @since  11.1
     */
    protected $values = null;

    /**
     * @var    db  The order element.
     * @since  11.1
     */
    protected $order = null;

    /**
     * @var   object  The auto increment insert field element.
     * @since 11.1
     */
    protected $autoIncrementField = null;

    /**
     * @var    db  The call element.
     * @since  12.1
     */
    protected $call = null;

    /**
     * @var    db  The exec element.
     * @since  12.1
     */
    protected $exec = null;

    /**
     * @var    db  The union element.
     * @since  12.1
     */
    protected $union = null;

    /**
     * @var    db  The unionAll element.
     * @since  13.1
     */
    protected $unionAll = null;


    protected $nameQuote;
    /**
     * Connect database
     * @return mixed
     */
    abstract public function dbConnect();

    abstract public function select($select = array());

    abstract public function from($table = "");

    abstract public function where($where = array());

    abstract public function orderBy($order = "");

    abstract public function groupBy($group = "");

    abstract public function getQuery($new = true);

    abstract public function setQuery($query);

    /**
     *
     * load data
     *
     * @return mixed
     */
    abstract public function loadObjects();

    abstract public function loadResult();

    abstract public function loadArray();

    abstract public function loadColumn();

    abstract public function loadRow();

    abstract public function __get($name);

    abstract public function __set($name, $value);

    /**
     * quote
     */
    abstract public function quote($text, $escape = true);

    abstract public function quoteName($name, $as = null);

    abstract protected function quoteNameStr($strArr);
}