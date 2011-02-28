<?php

require_once 'classes/Post.php';

/**
 * @property $commentText
 */
class Comment_Post extends Post
{
    protected $_fields = array
    (
        'commentText' => array
        (
            'required' => TRUE,
            'type' => 'string',
        ),
    );
}
