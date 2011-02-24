<?php

require_once 'classes/Post.php';

/**
 * @property $commentText
 */
class Comment_Post extends Post
{
    protected $fields = array
    (
        'commentText' => array
        (
            'required' => true,
            'type' => 'string',
        ),
    );
}
