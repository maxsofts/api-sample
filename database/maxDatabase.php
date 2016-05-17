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

    public function select($select = array())
    {
        $this->select = $select;
    }

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

    public function where($where = array())
    {
        $this->where = $where;
    }

    public function orderBy($order = "")
    {
        // TODO: Implement orderBy() method.
    }

    public function groupBy($group = "")
    {
        // TODO: Implement groupBy() method.
    }

    public function getQuery($new = true)
    {
        // TODO: Implement getQuery() method.
    }

    public function setQuery($query)
    {
        // TODO: Implement setQuery() method.
    }

    /**
     *
     * load data
     *
     * @return mixed
     */
    public function loadObjects()
    {
        // TODO: Implement loadObjects() method.
    }

    public function loadResult()
    {
        // TODO: Implement loadResult() method.
    }

    public function loadArray()
    {
        // TODO: Implement loadArray() method.
    }

    public function loadColumn()
    {
        // TODO: Implement loadColumn() method.
    }

    public function loadRow()
    {
        // TODO: Implement loadRow() method.
    }

    public function __get($name)
    {

        return isset($this->$name) ? $this->$name : null;
    }

    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
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

    protected function quoteNameStr($strArr)
    {
        $parts = array();
        $q = $this->nameQuote;

        foreach ($strArr as $part)
        {
            if (is_null($part))
            {
                continue;
            }

            if (strlen($q) == 1)
            {
                $parts[] = $q . $part . $q;
            }
            else
            {
                $parts[] = $q{0} . $part . $q{1};
            }
        }

        return implode('.', $parts);
    }
}