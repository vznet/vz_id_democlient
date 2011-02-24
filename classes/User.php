<?php

require_once 'classes/Cookie.php';
require_once 'classes/Config.php';
require_once 'classes/User_DB.php';

/**
 * @author gbittersmann
 */
class User
{
    private $_id = null;
    private $_name = '';

    /**
     *get user id
     *
     * @return integer user id
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * get user name
     *
     * @return string user name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * check if user is logged in
     *
     * @return boolean true if logged in
     */
    public function checkLogin()
    {
        $user_db = User_DB::getInstance();
        $config = Config::getInstance();
        
        $cookie = new Cookie();
        $cookieValue = $cookie->getValue($config->cookieKey);

        if ($cookieValue)
        {
            // check if VZ-ID cookie exists and is valid
            $cookieData = array();
            parse_str($cookieValue, $cookieData);
            $baseString = $cookieData['access_token'] . $cookieData['issued_at'] . $cookieData['user_id'];
            $signature = base64_encode(hash_hmac('sha1', $baseString, $config->consumerSecret, true));
            $retrievedSignature = $cookieData['signature'];

            // check if given signature equals calculated signature which used the
            // consumer secret in order to avoid user id manipulation
            if ($signature !== $retrievedSignature)
            {
                throw new User_Exception('Invalid cookie signature.', User_Exception::INVALID_COOKIE_SIGNATURE);
            }

            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $cookieData['user_id'] . '?oauth_token=' . $cookieData['access_token']);
            curl_setopt($curlHandle, CURLOPT_HTTPGET, true);
            curl_setopt($curlHandle, CURLOPT_HEADER, 0);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            $ret = curl_exec($curlHandle);
            curl_close($curlHandle);

            $userData = json_decode($ret, true);

            if (!empty($userData))
            {
                $id = $userData['entry']['id'];
                $name = $userData['entry']['displayName'];

                if (!empty($id) && !empty($name))
                {
                    $this->_id = $user_db->checkUser($id, $name);
                    $this->_name = $name;
                    return true;
                }
                else
                {
                    throw new User_Exception('VZ connection lost.', User_Exception::VZ_CONNECTION_LOST);
                }
            }
        }
        else
        {
            return false;
        }
    }
}

class User_Exception extends Exception
{
    const VZ_CONNECTION_LOST = 1;
    const INVALID_COOKIE_SIGNATURE = 2;
}
