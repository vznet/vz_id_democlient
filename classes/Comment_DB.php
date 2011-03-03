<?php

require_once 'classes/Config.php';
require_once 'classes/SQLite3_DB.php';
require_once 'classes/User_DB.php';

class Comment_DB extends SQLite3_DB
{
    /**
     *
     * @var Comment_DB
     */
    private static $_uniqueInstance = NULL;

    protected function __construct()
    {
        // make sure that user database exists
        $user_db = User_DB::getInstance();

        parent::__construct();

        $this->createTable();
    }

    /**
     * create comment table if not existent
     */
    public function createTable()
    {
        $result = $this->_db->query("SELECT name FROM sqlite_master WHERE name='Comments' AND type='table'");
        if (!$result->fetchArray(SQLITE3_ASSOC))
        {
            $this->_db->query('CREATE TABLE `Comments` (
                `commentId` INTEGER PRIMARY KEY,
                `created` INTEGER NOT NULL,
                `userId` SQLITE3_INTEGER NOT NULL,
                `commentText` SQLITE3_TEXT NOT NULL,
                `expires` INTEGER
            );');
        }
    }

    /**
     *
     * @return Comment_DB comment database unique instance
     */
    public static function getInstance()
    {
        if (self::$_uniqueInstance === NULL)
        {
            self::$_uniqueInstance = new Comment_DB();
        }
        return self::$_uniqueInstance;
    }

    /**
     * add a comment
     *
     * @param string $userId VZ user id
     * @param string $commentText comment text
     */
    public function addComment($userId, $commentText)
    {
        $config = Config::getInstance();
        $time = time();
        $expires = (isset($config->commentDuration) && $config->commentDuration != 0) ? $time + $config->commentDuration : 0;

        $stmt = $this->_db->prepare('INSERT INTO Comments VALUES(null, :created, :userId, :commentText, :expires)');
        $stmt->bindParam(':created', $time, SQLITE3_INTEGER);
        $stmt->bindParam(':userId', $userId, SQLITE3_INTEGER);
        $stmt->bindParam(':commentText', $commentText, SQLITE3_TEXT);
        $stmt->bindParam(':expires', $expires, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * delete a comment by id
     *
     * @param integer $commentId comment id
     */
    public function deleteCommentById($commentId)
    {
        $stmt = $this->_db->prepare('DELETE FROM Comments WHERE commentId = :commentId;');
        $stmt->bindParam(':commentId', $commentId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * delete all expired comments
     */
    public function deleteExpiredComments()
    {
        $time = time();
        $stmt = $this->_db->prepare('DELETE FROM Comments WHERE expires <> 0 AND expires < :expires;');
        $stmt->bindParam(':expires', $time, SQLITE3_INTEGER);
        $stmt->execute();
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
