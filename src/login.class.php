<?php
namespace EffectiveWPNonUsers;

class Login extends \EffectiveWPToolkit\Singleton
{
    const TOKEN_LENGTH = 100;
    const COOKIE_NAME_AUTH_TOKEN = 'ewn_authtoken';
    const COOKIE_NAME_ELEVATE_TOKEN = 'ewn_elevatetoken';
    
    protected function USERS()
    {
        return Users::get_instance();
    }

    function checkLogin($email, $password)
    {
        return $this->USERS()->checkLogin($email, $password);
    }

    function createAuthToken($length=self::TOKEN_LENGTH)
    {
        $token = bin2hex(random_bytes($length));
        return $token;
    }

    function getCurrentUser()
    {
        $token = !empty($_COOKIE[self::COOKIE_NAME_AUTH_TOKEN])?$_COOKIE[self::COOKIE_NAME_AUTH_TOKEN]:false;
        
        if (!empty($token))
        {
            $tokenInfo = $this->getTokenInfo($token);

            if (!empty($tokenInfo))
            {
                $userId = $tokenInfo->userId;
                return $this->getUserById($userId);
            }
        }


        return false;
    }

    function getUserById($id)
    {
        return $this->USERS()->getUser($id);
    }


    /* Cookie Management */
    function loginUser($email, $password, $clearOldTokens=true)
    {
        if ($user = $this->checkLogin($email,$password))
        {
            do {
                $token = $this->createAuthToken();
            } while ($this->getTokenInfo($token)!=false);

            if ($clearOldTokens)
                $this->removeUserTokens($user->getId());

            $this->storeToken($user->getId(), $token);
            setcookie(self::COOKIE_NAME_AUTH_TOKEN, $token, -1, '/');
        }
    }

    function forceLoginUser($email)
    {
        if ($user = $this->USERS()->getUserByEmail($email))
        {
            do {
                $token = $this->createAuthToken();
            } while ($this->getTokenInfo($token)!=false);

            if ($clearOldTokens)
                $this->removeUserTokens($user->getId());

            $this->storeToken($user->getId(), $token);
            setcookie(self::COOKIE_NAME_AUTH_TOKEN, $token, -1, '/');
        }
    }


    function logoutUser()
    {
        setcookie(self::COOKIE_NAME_AUTH_TOKEN, null, -1, '/');
        setcookie(self::COOKIE_NAME_ELEVATE_TOKEN, null, -1, '/');
    }

    /* End Cookie Management */

    /* User Switching */
    function switchToUser($userId)
    {

    }

    function restoreUser()
    {

    }
    /* End User Switching */

    /* Token Management */
    function removeUserTokens($userId)
    {
        global $wpdb;
        $sql = 'DELETE FROM ' . $wpdb->prefix . EWN_Schema::NONUSER_AUTH_TOKENS . ' WHERE userId=%d';
        $sql = $wpdb->prepare($sql, $userId);

        $wpdb->query($sql);
    }

    function getTokenInfo($token)
    {
        global $wpdb;
        $sql = 'SELECT * FROM ' . $wpdb->prefix . EWN_Schema::NONUSER_AUTH_TOKENS . ' WHERE token = %s LIMIT 1';
        $sql = $wpdb->prepare($sql, $token);

        $result = $wpdb->get_row($sql);

        return $result;
    }

    function storeToken($userId, $token, $created=false)
    {
        if ($this->getTokenInfo($token))
        {
            throw new AuthTokenAlreadyExistsException();
            return false;
        }

        if ($created===false)
            $created = current_time('mysql');

        global $wpdb;
        $sql = 'INSERT INTO ' . $wpdb->prefix . EWN_Schema::NONUSER_AUTH_TOKENS . ' (userId, token, created) VALUES (%d, %s, %s)';
        $sql = $wpdb->prepare($sql, $userId, $token, $created);

        $wpdb->query($sql);
        return $wpdb->insert_id;
    }
}



class AuthTokenAlreadyExistsException extends \Exception
{
    function __construct($message='Auth token already exists', $code=0)
    {
        parent::__construct($message, $code);
    }
}