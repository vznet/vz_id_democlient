<?php

class SQLite3_DB
{
    const DB_FILENAME = 'demo.db';

    /**
     *
     * @var SQLite3
     */
    protected $_db = NULL;

    /**
     *
     * @param string $db_filename data base file name
     */
    protected function __construct($db_filename)
    {
        if ($this->_db === NULL)
        {
            $this->_db = new SQLite3($db_filename);
        }
    }
}

class SQLite3_DB_Exception extends Exception {}
