<?php
    require_once 'classes/Config.php';
    $config = Config::getInstance();

    $res = setcookie($config->cookieKey, '', 0);
    header('Location: ' . $config->indexUrl);
?>