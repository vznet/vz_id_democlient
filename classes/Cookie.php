<?php

class Cookie
{
    /**
     * get cookie value
     * 
     * @param string $key cookie key
     * @return string cookie value 
     */
    public function getValue($key)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    }
}
