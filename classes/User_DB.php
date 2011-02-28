<?php

require_once 'classes/SQLite3_DB.php';

class User_DB extends SQLite3_DB
{
    private static $_uniqueInstance = NULL;

    protected function __construct()
    {
        parent::__construct(self::DB_FILENAME);

        $result = $this->_db->query("SELECT name FROM sqlite_master WHERE name='Users' AND type='table'");

        if (!$result->fetchArray(SQLITE3_ASSOC))
        {
            $this->_db->query('CREATE TABLE `Users` (
                `userId` INTEGER PRIMARY KEY,
                `name` SQLITE3_TEXT NOT NULL,
                `vzId` SQLITE3_TEXT NOT NULL
            );');
        }
    }

    /**
     *
     * @return User_DB user database unique instance
     */
    public static function getInstance()
    {
        if (self::$_uniqueInstance === NULL)
        {
            self::$_uniqueInstance = new User_DB();
        }
        return self::$_uniqueInstance;
    }

    /**
     *
     * @param string $name user name
     * @param string $vzId VZ id
     * @return integer user id
     */
    public function addUser($name, $vzId)
    {
        $stmt = $this->_db->prepare('INSERT INTO Users VALUES (NULL, :name, :vzId)');
        $stmt->bindParam(':name', $name, SQLITE3_TEXT);
        $stmt->bindParam(':vzId', $vzId, SQLITE3_TEXT);
        $stmt->execute();
        return $this->_db->lastInsertRowID();        
    }
    
     /**
     *
     * @param integer $userId user id
     * @return array user
     */
    public function getUserById($userId)
    {
        $stmt = $this->_db->prepare('SELECT * FROM Users WHERE userId=:userId');
        $stmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        if ($user = $result->fetchArray())
        {
            return $user;
        }
    }

     /**
     *
     * @param string $vzId VZ id
     * @return array user
     */
    public function getUserByVzId($vzId)
    {
        $stmt = $this->_db->prepare('SELECT * FROM Users WHERE vzId=:vzId');
        $stmt->bindValue(':vzId', $vzId, SQLITE3_TEXT);
        $result = $stmt->execute();

        if ($user = $result->fetchArray())
        {
            return $user;
        }
    }

    /**
     *
     * @param integer $userId user id
     * @param string $name user name
     */
    public function updateUserName($userId, $name)
    {
        $stmt = $this->_db->prepare('UPDATE Users SET name = :name WHERE userId=:userId');
        $stmt->bindParam(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
        $stmt->execute();
    }
 }

class User_DB_Exception extends SQLite3_DB_Exception {}
