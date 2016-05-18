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


    protected $nameQuote = '`';


    protected $data;

    /**
     * Connect database
     * @return mixed
     */
    abstract public function dbConnect();

    abstract public function select($select = array());

    /**
     * Add a JOIN clause to the query.
     *
     * Usage:
     * $query->join('INNER', 'b ON b.id = a.id);
     *
     */
    abstract public function join($type, $conditions);

    abstract public function from($table = "");

    abstract public function where($where = array());

    abstract public function group($columns);

    abstract public function having($conditions, $glue = 'AND');

    /**
     * Add a table name to the UPDATE clause of the query.
     *
     * Note that you must not mix insert, update, delete and select method calls when building a query.
     *
     * Usage:
     * $query->update('#__foo')
     *
     */
    abstract public function update($table);

    /**
     * Add a single condition string, or an array of strings to the SET clause of the query.
     *
     * Usage:
     * $query->set('a = 1')->set('b = 2');
     *
     * @param   mixed   $conditions  A string or array of string conditions.
     * @param   string  $glue        The glue by which to join the condition strings. Defaults to ,.
     *                               Note that the glue is set on first use and cannot be changed.
     */
    abstract public function set($conditions, $glue = ',');

    /**
     * Add a ordering column to the ORDER clause of the query.
     *
     * Usage:
     * $query->order('foo')
     * $query->order(array('foo','bar'));
     */
    abstract public function order($columns);

    /**
     * Add a query to UNION with the current query.
     * Multiple unions each require separate statements and create an array of unions.
     *
     * Usage (the $query base query MUST be a select query):
     * $query->union('SELECT name FROM  #__foo')
     * $query->union('SELECT name FROM  #__foo', true)
     * $query->union(array('SELECT name FROM  #__foo','SELECT name FROM  #__bar'))
     * $query->union(($query2)->union($query3))
     * $query->union(array($query2, $query3))
     */
    abstract public function union($query, $distinct = false, $glue = '');

    /**
     * @param bool|true $new
     * @return mixed
     */
    abstract public function getQuery();

    abstract public function setQuery();

    /**
     *
     * load data
     *
     * @return mixed
     */
    abstract public function loadObjects();

    abstract public function loadResult();

    abstract public function loadArray();

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