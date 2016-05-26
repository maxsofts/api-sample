<?php
namespace max_api\contracts\database;

interface dbImplement
{

    /**
     * Connect database
     * @return mixed
     */
    public function dbConnect();

    /**
     * @param array $select
     * @return mixed
     */
    public function select($select = array());

    /**
     * Add a JOIN clause to the query.
     *
     * Usage:
     * $query->join('INNER', 'b ON b.id = a.id);
     *
     */
    public function join($type, $conditions);

    /**
     * @param string $table
     * @return mixed
     */
    public function from($table = "");

    /**
     * @param array $where
     * @return mixed
     */
    public function where($where = array());

    /**
     * @param $columns
     * @return mixed
     */
    public function group($columns);

    /**
     * @param $conditions
     * @param string $glue
     * @return mixed
     */
    public function having($conditions, $glue = 'AND');

    /**
     * Add a table name to the UPDATE clause of the query.
     *
     * Note that you must not mix insert, update, delete and select method calls when building a query.
     *
     * Usage:
     * $query->update('#__foo')
     *
     */
    public function update($table);

    /**
     * Add a table name to the INSERT clause of the query.
     *
     * Note that you must not mix insert, update, delete and select method calls when building a query.
     *
     * Usage:
     * $query->insert('#__a')->set('id = 1');
     * $query->insert('#__a')->columns('id, title')->values('1,2')->values('3,4');
     * $query->insert('#__a')->columns('id, title')->values(array('1,2', '3,4'));
     */
    public function insert($table, $incrementField = false);

    /**
     * Adds a column, or array of column names that would be used for an INSERT INTO statement.
     */
    public function columns($columns);

    /**
     * Adds a tuple, or array of tuples that would be used as values for an INSERT INTO statement.
     *
     * Usage:
     * $query->values('1,2,3')->values('4,5,6');
     * $query->values(array('1,2,3', '4,5,6'));
     */
    public function values($values);

    /**
     * Add a single condition string, or an array of strings to the SET clause of the query.
     *
     * Usage:
     * $query->set('a = 1')->set('b = 2');
     *
     * @param   mixed $conditions A string or array of string conditions.
     * @param   string $glue The glue by which to join the condition strings. Defaults to ,.
     *                               Note that the glue is set on first use and cannot be changed.
     */
    public function set($conditions, $glue = ',');

    /**
     * Add a ordering column to the ORDER clause of the query.
     *
     * Usage:
     * $query->order('foo')
     * $query->order(array('foo','bar'));
     */
    public function order($columns);

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
    public function union($query, $distinct = false, $glue = '');

    /**
     * @return mixed
     */
    public function getQuery();

    /**
     * @return mixed
     */
    public function setQuery();

    /**
     * @return mixed
     */
    public function getSql();

    /**
     *
     * load data
     *
     * @return mixed
     */
    public function loadObjects();

    /**
     * @return mixed
     */
    public function loadObject();

    /**
     * @return mixed
     */
    public function loadResult();

    /**
     * @return mixed
     */
    public function loadArray();

    /**
     * @return mixed
     */
    public function loadArrays();

    /**
     * @return mixed
     */
    public function loadAssoc();

    /**
     * @return mixed
     */
    public function loadAssocs();

    /**
     * @return mixed
     */
    public function loadRow();

    /**
     * @return mixed
     */
    public function loadRows();

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name);

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value);

    /**
     * quote
     */
    public function quote($text, $escape = true);

    /**
     * @param $name
     * @param null $as
     * @return mixed
     */
    public function quoteName($name, $as = null);

    /**
     * @param $name
     * @param $elements
     * @return mixed
     */
    public function append($name, $elements);


    /**
     * @param $text
     * @param bool|false $extra
     * @return mixed
     */
    public function escape($text, $extra = false);

}