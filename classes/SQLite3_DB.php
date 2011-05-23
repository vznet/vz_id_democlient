<?php

class SQLite3_DB
{
    const DB_FILENAME = '/tmp/demo.db';

    /**
     *
     * @var SQLite3
     */
    protected $_db = NULL;

    protected function __construct()
    {
        if ($this->_db === NULL)
        {
            $this->_db = new SQLite3(self::DB_FILENAME);
        }
    }
}

class SQLite3_DB_Exception extends Exception {}
