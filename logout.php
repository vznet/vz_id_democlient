<?php
    require_once 'classes/Config.php';
    $config = Config::getInstance();

    setcookie($config->cookieKey, '', 0);
    header('Location: ' . $config->indexUrl);
?>