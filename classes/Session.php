<?php

require_once 'classes/Config.php';
require_once 'classes/Cookie.php';
require_once 'classes/User_DB.php';

class Session
{
    public function getJanRainUser()
    {
        $user_db = User_DB::getInstance();
        $config  = Config::getInstance();

        $cookie = new Cookie();

        if (isset($_POST['token']) && strlen($_POST['token']) == 40) {
            $cookie->setValue($config->cookieKeyJanRain, $_POST['token']);
            header('Location: ' . $config->indexUrl);
            die();
        }

        $cookieToken = $cookie->getValue($config->cookieKeyJanRain);

        if ($cookieToken) {//test the length of the token; it should be 40 characters
          /* STEP 2: Use the token to make the auth_info API call */
          $postData = array('token'  => $cookieToken,
                             'apiKey' => $config->rpxApiKey,
                             'format' => 'json',
                             'extended' => 'false'); //Extended is not available to Basic.
          $curl = curl_init();
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($curl, CURLOPT_URL, 'https://rpxnow.com/api/v2/auth_info');
          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
          curl_setopt($curl, CURLOPT_HEADER, false);
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($curl, CURLOPT_FAILONERROR, true);
          $result = curl_exec($curl);
          if ($result == false){
            echo "\n".'Curl error: ' . curl_error($curl);
            echo "\n".'HTTP code: ' . curl_errno($curl);
            echo "\n"; var_dump($postData);
          }
          curl_close($curl);


          /* STEP 3: Parse the JSON auth_info response */
          $authInfo = json_decode($result, true);

          if ($authInfo['stat'] == 'ok') {
                $userData = array
                (
                    //we have to set id to 0, because JanRain never returns id
                    0 => 0,
                    'userId' => 0,
                    1 => $authInfo['profile']['displayName'],
                    'name' => $authInfo['profile']['displayName'],
                    2 => $authInfo['profile']['identifier'],
                    'vzId' => $authInfo['profile']['identifier']
                );

                $user = $user_db->getUserByVzId($userData['vzId']);

                if(!$user)
                {
                    $userId = $user_db->addUser($userData['name'], $userData['userId']);
                    $user = $user_db->getUserById($userId);
                }

                // update user name if it has been changed
                if ($user['name'] != $userData['name'])
                {
                    $user_db->updateUserName($user['userId'], $userData['name']);
                }
                return $user;
            } else {
              // Gracefully handle auth_info error.  Hook this into your native error handling system.
              echo "\n".'An error occured: ' . $authInfo['err']['msg']."\n";
              var_dump($authInfo);
              echo "\n";
              var_dump($result);
            }
        }
    }

    /**
     * check if user is logged in
     *
     * @return array user
     */
    public function getCurrentUser()
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
            // check if given signature equals calculated signature which used the consumer secret in order to avoid user id manipulation
            if ($signature !== str_replace(' ', '+', $cookieData['signature']))
            {
                throw new Session_Exception('Invalid cookie signature.', Session_Exception::INVALID_COOKIE_SIGNATURE);
            }

            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $cookieData['user_id'] . '?oauth_token=' . $cookieData['access_token']);
            curl_setopt($curlHandle, CURLOPT_HTTPGET, true);
            curl_setopt($curlHandle, CURLOPT_HEADER, 0);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curlHandle);
            curl_close($curlHandle);

            $userData = json_decode($result, true);
            
            if(empty($userData) || !isset($userData['entry']) ||!isset($userData['entry']['id']) ||!isset($userData['entry']['displayName']))
            {
                throw new Session_Exception('Invalid user data.', Session_Exception::INVALID_USER_DATA);
            }
            
            if (empty($userData['entry']['id']) || empty($userData['entry']['displayName']))
            {
                throw new Session_Exception('VZ connection lost.', Session_Exception::VZ_CONNECTION_LOST);
            }
            
            $user = $user_db->getUserByVzId($userData['entry']['id']);

            if(!$user)
            {
                $userId = $user_db->addUser($userData['entry']['displayName'], $userData['entry']['id']);
                $user = $user_db->getUserById($userId);
            }

            // update user name if it has been changed
            if ($user['name'] != $userData['entry']['displayName'])
            {
                $user_db->updateUserName($user['userId'], $userData['entry']['displayName']);
            }
            return $user;
        }
    }
}

class Session_Exception extends Exception
{
    const INVALID_COOKIE_SIGNATURE = 1;
    const INVALID_USER_DATA = 2;
    const VZ_CONNECTION_LOST = 3;
}
