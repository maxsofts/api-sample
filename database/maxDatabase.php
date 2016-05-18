<?php

namespace max_api\database;

use max_api\contracts\config;
use max_api\contracts\database\databaseElement;
use max_api\contracts\database\db;

/**
 * Class maxDatabase
 * @package max_api\database
 */
class maxDatabase extends db
{


    /**
     * maxDatabase constructor.
     */
    public function __construct()
    {
        $this->dbConnect();
    }


    /**
     * Database Connect
     */
    public function dbConnect()
    {
        $config = config::get('config.driver');

        $connect = mysqli_connect($config['host'], $config['username'], $config['password'], $config['dbName']);
        // check connection
        if ($connect->connect_error) {
            throw new Exception('Database connection failed: ' . $connect->connect_error);
        }
        $this->db = $connect;
    }

    /**
     * Appends element parts to the internal list.
     *
     * @param mixed $name this key
     * @param   mixed $elements String or array
     * @return  void
     */
    public function append($name, $elements)
    {
        if (is_array($elements)) {
            $this->$name = array_merge($this->$name, $elements);
        } else {
            $this->$name = array_merge($this->$name, array($elements));
        }

    }

    /**
     * @param array $select
     */
    public function select($select = array())
    {
        if (is_null($this->select)) {
            $this->select = new databaseElement('SELECT', $select);
        } else {
            $this->select->append($select);
        }

        return $this;
    }

    /**
     * @param string $tables
     * @param null $subQueryAlias
     * @return $this
     * @throws RuntimeException
     */
    public function from($tables = "", $subQueryAlias = null)
    {
        if (is_null($this->from)) {
            if ($tables instanceof $this) {
                if (is_null($subQueryAlias)) {
                    throw new RuntimeException('NULL SUB QUERY ALIAS');
                }

                $tables = '( ' . (string)$tables . ' ) AS ' . $this->quoteName($subQueryAlias);
            }

            $this->from = new databaseElement('FROM', $tables);
        } else {
            $this->from->append($tables);
        }

        return $this;
    }

    /**
     * @param array $where
     * @param string $glue
     */
    public function where($where = array(), $glue = "AND")
    {
        if (is_null($this->where)) {
            $glue = strtoupper($glue);
            $this->where = new databaseElement('WHERE', $where, " $glue ");
        } else {
            $this->where->append($where);
        }

        return $this;
    }

    /**
     * @param $type
     * @param $conditions
     * @return $this
     */
    public function join($type, $conditions)
    {
        if (is_null($this->join)) {
            $this->join = array();
        }

        $this->join[] = new databaseElement(strtoupper($type) . ' JOIN', $conditions);

        return $this;
    }

    /**
     * @param $columns
     * @return $this
     */
    public function group($columns)
    {
        if (is_null($this->group)) {
            $this->group = new databaseElement('GROUP BY', $columns);
        } else {
            $this->group->append($columns);
        }

        return $this;
    }

    /**
     * @param $conditions
     * @param string $glue
     * @return $this
     */
    public function having($conditions, $glue = 'AND')
    {
        if (is_null($this->having)) {
            $glue = strtoupper($glue);
            $this->having = new databaseElement('HAVING', $conditions, " $glue ");
        } else {
            $this->having->append($conditions);
        }

        return $this;
    }

    /**
     * @param $columns
     * @return $this
     */
    public function order($columns)
    {
        if (is_null($this->order)) {
            $this->order = new databaseElement('ORDER BY', $columns);
        } else {
            $this->order->append($columns);
        }

        return $this;
    }

    /**
     * @param $query
     * @param bool|false $distinct
     * @param string $glue
     * @return $this
     */
    public function union($query, $distinct = false, $glue = '')
    {
        // Set up the DISTINCT flag, the name with parentheses, and the glue.
        if ($distinct) {
            $name = 'UNION DISTINCT ()';
            $glue = ')' . PHP_EOL . 'UNION DISTINCT (';
        } else {
            $glue = ')' . PHP_EOL . 'UNION (';
            $name = 'UNION ()';
        }

        // Get the JDatabaseQueryElement if it does not exist
        if (is_null($this->union)) {
            $this->union = new databaseElement($name, $query, " $glue ");
        } // Otherwise append the second UNION.
        else {
            $this->union->append($query);
        }

        return $this;
    }

    /**
     * @param $table
     * @return $this
     */
    public function update($table)
    {
        $this->type = 'update';
        $this->update = new databaseElement('UPDATE', $table);

        return $this;
    }

    /**
     * @param mixed $conditions
     * @param string $glue
     * @return $this
     */
    public function set($conditions, $glue = ',')
    {
        if (is_null($this->set)) {
            $glue = strtoupper($glue);
            $this->set = new databaseElement('SET', $conditions, "\n\t$glue ");
        } else {
            $this->set->append($conditions);
        }

        return $this;
    }

    public function getQuery()
    {
        $this->clear();

        return $this;
    }

    /**
     * Clear data from the query or a specific clause of the query.
     *
     * @param   string $clause Optionally, the name of the clause to clear, or nothing to clear the whole query.
     *
     * @return $this
     */
    public function clear($clause = null)
    {
        $this->sql = null;

        switch ($clause)
        {
            case 'select':
                $this->select = null;
                $this->type = null;
                break;

            case 'delete':
                $this->delete = null;
                $this->type = null;
                break;

            case 'update':
                $this->update = null;
                $this->type = null;
                break;

            case 'insert':
                $this->insert = null;
                $this->type = null;
                $this->autoIncrementField = null;
                break;

            case 'from':
                $this->from = null;
                break;

            case 'join':
                $this->join = null;
                break;

            case 'set':
                $this->set = null;
                break;

            case 'where':
                $this->where = null;
                break;

            case 'group':
                $this->group = null;
                break;

            case 'having':
                $this->having = null;
                break;

            case 'order':
                $this->order = null;
                break;

            case 'columns':
                $this->columns = null;
                break;

            case 'values':
                $this->values = null;
                break;

            case 'exec':
                $this->exec = null;
                $this->type = null;
                break;

            case 'call':
                $this->call = null;
                $this->type = null;
                break;

            case 'limit':
                $this->offset = 0;
                $this->limit = 0;
                break;

            case 'offset':
                $this->offset = 0;
                break;

            case 'union':
                $this->union = null;
                break;

            case 'unionAll':
                $this->unionAll = null;
                break;

            default:
                $this->type = null;
                $this->select = null;
                $this->delete = null;
                $this->update = null;
                $this->insert = null;
                $this->from = null;
                $this->join = null;
                $this->set = null;
                $this->where = null;
                $this->group = null;
                $this->having = null;
                $this->order = null;
                $this->columns = null;
                $this->values = null;
                $this->autoIncrementField = null;
                $this->exec = null;
                $this->call = null;
                $this->union = null;
                $this->unionAll = null;
                $this->offset = 0;
                $this->limit = 0;
                break;
        }

        return $this;
    }

    public function setQuery()
    {
        if (is_null($this->sql)) {
            $this->sql = $this->getSql();
        }

        $results = $this->db->query($this->sql);

        if ($results) {
            $this->data = $results;
        }

        return $this;
    }

    public function setUpdate()
    {
        try {
            /* switch autocommit status to FALSE. Actually, it starts transaction */
            $this->db->autocommit(FALSE);


            $res = $this->setQuery();

            if ($res === false) {

                return false;
            }
            $this->db->commit();
            $this->db->autocommit(TRUE);

            return true;
        } catch (Exception $e) {

            $this->db->rollback();
            $this->db->autocommit(TRUE);
            return false;
        }
    }

    public function setInsert()
    {
        try {
            /* switch autocommit status to FALSE. Actually, it starts transaction */
            $this->db->autocommit(FALSE);
            $res = $this->db->query($this->getSql());
            if ($res === false) {
                return false;
            }
            $this->db->commit();
            $this->db->autocommit(TRUE);

            return $this->db->insert_id();
        } catch (Exception $e) {
            $this->db->rollback();
            $this->db->autocommit(TRUE);
            return false;
        }
    }

    /**
     *
     * load data
     *
     * @return mixed
     */
    public function loadObjects()
    {
        if ($this->data) {
            return $this->data->fetch_object();
        }
    }

    public function loadResult()
    {
        return $this->loadArray()[0];
    }

    public function loadArray()
    {
        if ($this->data) {
            return $this->data->fetch_array();
        }
    }

    public function loadAssoc()
    {
        if ($this->data) {
            return $this->data->fetch_assoc();
        }
    }

    public function loadRow()
    {
        if ($this->data) {
            return $this->data->fetch_row();
        }
    }

    public function __get($name)
    {

        return isset($this->$name) ? $this->$name : null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function quote($text, $escape = true)
    {

        if (is_array($text)) {
            foreach ($text as $k => $v) {
                $text[$k] = $this->quote($v, $escape);
            }

            return $text;
        } else {
            return '\'' . ($escape ? $this->escape($text) : $text) . '\'';
        }
    }

    public function quoteName($name, $as = null)
    {
        if (is_string($name)) {
            $quotedName = $this->quoteNameStr(explode('.', $name));

            $quotedAs = '';

            if (!is_null($as)) {
                settype($as, 'array');
                $quotedAs .= ' AS ' . $this->quoteNameStr($as);
            }

            return $quotedName . $quotedAs;
        } else {
            $fin = array();

            if (is_null($as)) {
                foreach ($name as $str) {
                    $fin[] = $this->quoteName($str);
                }
            } elseif (is_array($name) && (count($name) == count($as))) {
                $count = count($name);

                for ($i = 0; $i < $count; $i++) {
                    $fin[] = $this->quoteName($name[$i], $as[$i]);
                }
            }

            return $fin;
        }
    }

    /**
     * @return string QUERY
     */
    public function getSql()
    {
        $query = '';

        if ($this->sql) {
            return $this->sql;
        }

        switch ($this->type) {
            case 'element':
                $query .= (string)$this->element;
                break;

            case 'select':
                $query .= (string)$this->select;
                $query .= (string)$this->from;

                if ($this->join) {
                    // Special case for joins
                    foreach ($this->join as $join) {
                        $query .= (string)$join;
                    }
                }

                if ($this->where) {
                    $query .= (string)$this->where;
                }

                if ($this->group) {
                    $query .= (string)$this->group;
                }

                if ($this->having) {
                    $query .= (string)$this->having;
                }

                if ($this->order) {
                    $query .= (string)$this->order;
                }

                if ($this->union) {
                    $query .= (string)$this->union;
                }

                break;

            case 'delete':
                $query .= (string)$this->delete;
                $query .= (string)$this->from;

                if ($this->join) {
                    // Special case for joins
                    foreach ($this->join as $join) {
                        $query .= (string)$join;
                    }
                }

                if ($this->where) {
                    $query .= (string)$this->where;
                }

                if ($this->order) {
                    $query .= (string)$this->order;
                }

                break;

            case 'update':
                $query .= (string)$this->update;

                if ($this->join) {
                    // Special case for joins
                    foreach ($this->join as $join) {
                        $query .= (string)$join;
                    }
                }

                $query .= (string)$this->set;

                if ($this->where) {
                    $query .= (string)$this->where;
                }

                if ($this->order) {
                    $query .= (string)$this->order;
                }

                break;

            case 'insert':
                $query .= (string)$this->insert;

                // Set method
                if ($this->set) {
                    $query .= (string)$this->set;
                } // Columns-Values method
                elseif ($this->values) {
                    if ($this->columns) {
                        $query .= (string)$this->columns;
                    }

                    $elements = $this->values->getElements();

                    if (!($elements[0] instanceof $this)) {
                        $query .= ' VALUES ';
                    }

                    $query .= (string)$this->values;
                }

                break;

            case 'call':
                $query .= (string)$this->call;
                break;

            case 'exec':
                $query .= (string)$this->exec;
                break;
        }

        return $query;
    }

    protected function quoteNameStr($strArr)
    {
        $parts = array();
        $q = $this->nameQuote;

        foreach ($strArr as $part) {
            if (is_null($part)) {
                continue;
            }

            if (strlen($q) == 1) {
                $parts[] = $q . $part . $q;
            } else {
                $parts[] = $q{0} . $part . $q{1};
            }
        }

        return implode('.', $parts);
    }

    /**
     * Method to escape a string for usage in an SQL statement.
     *
     * @param   string $text The string to be escaped.
     * @param   boolean $extra Optional parameter to provide extra escaping.
     *
     * @return  string  The escaped string.
     *
     */
    public function escape($text, $extra = false)
    {

        $result = mysqli_real_escape_string($this->db, $text);

        if ($extra) {
            $result = addcslashes($result, '%_');
        }

        return $result;
    }


}