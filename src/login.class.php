<?php
namespace EffectiveWPNonUsers;

class Login
{

    const TOKEN_LENGTH = 100;
    const COOKIE_NAME_AUTH_TOKEN = 'ewn_authtoken';
    const COOKIE_NAME_ELEVATE_TOKEN = 'ewn_elevatetoken';
    
    static function checkLogin($email, $password)
    {
        return Users::checkLogin($email, $password);
    }

    static function createAuthToken($length=self::TOKEN_LENGTH)
    {
        $token = bin2hex(random_bytes($length));
        return $token;
    }

    static function getCurrentUser()
    {
        $token = !empty($_COOKIE[self::COOKIE_NAME_AUTH_TOKEN])?$_COOKIE[self::COOKIE_NAME_AUTH_TOKEN]:false;
        
        if (!empty($token))
        {
            $tokenInfo = static::getTokenInfo($token);

            if (!empty($tokenInfo))
            {
                $userId = $tokenInfo->userId;
                return static::getUserById($userId);
            }
        }


        return false;
    }

    static function getUserById($id)
    {
        return Users::getUser($id);
    }


    /* Cookie Management */
    static function loginUser($email, $password)
    {
        if ($user = self::checkLogin($email,$password))
        {
            do {
                $token = self::createAuthToken();
            } while (self::getTokenInfo($token)!=false);

            self::removeUserTokens($user->getId());

            self::storeToken($user->getId(), $token);
            setcookie(self::COOKIE_NAME_AUTH_TOKEN, $token, -1, '/');
        }
    }


    static function logoutUser()
    {
        setcookie(self::COOKIE_NAME_AUTH_TOKEN, null, -1, '/');
        setcookie(self::COOKIE_NAME_ELEVATE_TOKEN, null, -1, '/');
    }

    /* End Cookie Management */

    /* User Switching */
    static function switchToUser($userId)
    {

    }

    static function restoreUser()
    {

    }
    /* End User Switching */

    /* Token Management */
    static function removeUserTokens($userId)
    {
        global $wpdb;
        $sql = 'DELETE FROM ' . $wpdb->prefix . EWN_Schema::NONUSER_AUTH_TOKENS . ' WHERE userId=%d';
        $sql = $wpdb->prepare($sql, $userId);

        $wpdb->query($sql);
    }

    static function getTokenInfo($token)
    {
        global $wpdb;
        $sql = 'SELECT * FROM ' . $wpdb->prefix . EWN_Schema::NONUSER_AUTH_TOKENS . ' WHERE token = %s LIMIT 1';
        $sql = $wpdb->prepare($sql, $token);

        $result = $wpdb->get_row($sql);

        return $result;
    }

    static function storeToken($userId, $token, $created=false)
    {
        if (self::getTokenInfo($token))
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