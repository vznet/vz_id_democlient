<?php

require_once 'classes/Config.php';

class SQLite3_DB
{
    /**
     *
     * @var SQLite3
     */
    protected $_db = NULL;

    protected function __construct()
    {
        $config = Config::getInstance();

        if ($this->_db === NULL)
        {
            $this->_db = new SQLite3($config->db_filename);
        }
    }
}

class SQLite3_DB_Exception extends Exception {}
