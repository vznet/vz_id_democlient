<?php

require_once 'classes/SQLite3_DB.php';

class User_DB extends SQLite3_DB
{
    const DB_FILENAME = 'users.db';

    private static $uniqueInstance = NULL;

    protected function __construct()
    {
        parent::__construct(self::DB_FILENAME);

        $result = $this->_db->query("SELECT name FROM sqlite_master WHERE name='Users' AND type='table'");

        if (!$result->fetchArray(SQLITE3_ASSOC))
        {
            $this->_db->query('CREATE TABLE `Users` (
                `userId` INTEGER PRIMARY KEY,
                `name` TIMESTAMP NOT NULL,
                `vzId` TEXT NOT NULL
            );');
        }
    }

    public function __destruct()
    {
        parent::__destruct();

        self::$uniqueInstance = NULL;
    }

    public static function getInstance()
    {
        if (self::$uniqueInstance === NULL)
        {
            self::$uniqueInstance = new User_DB();
        }
        return self::$uniqueInstance;
    }

    public function checkUser($vzId, $name)
    {
        $stmt = $this->_db->prepare('SELECT * FROM Users WHERE vzId=:vzId');
        $stmt->bindValue(':vzId', $vzId, SQLITE3_TEXT);
        $result = $stmt->execute();

        if ($user = $result->fetchArray())
        {
            if ($user['name'] != $name)
            {
                $stmt = $this->_db->prepare('UPDATE Users SET name = :name WHERE vzId=:vzId');
                $stmt->bindParam(':name', $name, SQLITE3_TEXT);
                $stmt->bindValue(':vzId', $vzId, SQLITE3_TEXT);
                $stmt->execute();
            }
            return $user['userId'];
        }
        else
        {
            $stmt = $this->_db->prepare('INSERT INTO Users VALUES (null, :name, :vzId)');
            $stmt->bindParam(':name', $name, SQLITE3_TEXT);
            $stmt->bindParam(':vzId', $vzId, SQLITE3_TEXT);
            $stmt->execute();
            return $this->_db->lastInsertRowID();
        }
    }
}

class User_DB_Exception extends SQLite3_DB_Exception {}
