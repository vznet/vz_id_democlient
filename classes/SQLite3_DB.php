<?php

class SQLite3_DB
{
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

    public function __destruct()
    {
        if ($this->_db !== NULL)
        {
            $this->_db->close();
        }

        $this->_db = NULL;
    }
}

class SQLite3_DB_Exception extends Exception {}
