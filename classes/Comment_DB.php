<?php

require_once 'classes/SQLite3_DB.php';

class Comment_DB extends SQLite3_DB
{
    const DB_FILENAME = 'comments.db';

    private static $uniqueInstance = NULL;

    protected function __construct()
    {
        parent::__construct(self::DB_FILENAME);

        $result = $this->_db->query("SELECT name FROM sqlite_master WHERE name='Comments' AND type='table'");

        if (!$result->fetchArray(SQLITE3_ASSOC))
        {
            $this->_db->query('CREATE TABLE `Comments` (
                `commentId` INTEGER PRIMARY KEY,
                `timestamp` TIMESTAMP NOT NULL,
                `userId` INTEGER NOT NULL,
                `commentText` TEXT NOT NULL
            );');
        }
    }

    public function  __destruct()
    {
        parent::__destruct();

        self::$uniqueInstance = NULL;
    }

    public static function getInstance()
    {
        if (self::$uniqueInstance === NULL)
        {
            self::$uniqueInstance = new Comment_DB();
        }
        return self::$uniqueInstance;
    }

        /**
     * add a comment
     *
     * @param string $userId VZ user id
     * @param string $commentText comment text
     */
    public function addComment($userId, $commentText)
    {
        $time = time();
        $insertStmt = $this->_db->prepare('INSERT INTO Comments VALUES(null, :timestamp, :userId, :commentText)');
        $insertStmt->bindParam(':timestamp', $time, SQLITE3_INTEGER);
        $insertStmt->bindParam(':userId', $userId, SQLITE3_INTEGER);
        $insertStmt->bindParam(':commentText', $commentText, SQLITE3_TEXT);
        $insertStmt->execute();
    }

    /**
     * get a comment by id
     *
     * @param integer $commentId comment id
     * @return array
     */
    public function getCommentById($commentId)
    {
        $stmt = $this->_db->prepare('SELECT * FROM Comments LEFT JOIN Users ON (Comments.userId = Users.userId) WHERE commentId = :commentId;');
        $stmt->bindParam(':commentId', $commentId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $comment = $result->fetchArray(SQLITE3_ASSOC);
        return $comment;
    }

    /**
     * get all comments
     *
     * @param string $order sorting order: 'ASC' or 'DESC' (default)
     * @return array
     */
    public function getComments($order = 'DESC')
    {
        $stmt = $this->_db->prepare('SELECT * FROM Comments LEFT JOIN Users ON (Comments.userId = Users.userId) ORDER BY commentId ' . $order . ';');
        $result = $stmt->execute();
        $commentArray = array();
        while ($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $commentArray[] = $row;
        }
        return $commentArray;
    }
}

class Comment_DB_Exception extends SQLite3_DB_Exception {}
