<?php
namespace EffectiveWPNonUsers;

use EffectiveWPToolkit\Singleton;

class Login extends Singleton
{
    const TOKEN_LENGTH = 100;
    const COOKIE_NAME_AUTH_TOKEN = 'ewn_authtoken';


    /**
     * Returns the object to be used for user management
     *
     * @return UsersManager
     */
    function getUsersManagerObject()
    {
        return UsersManager::instance();
    }


    function createToken($length=self::TOKEN_LENGTH)
    {
        $token = bin2hex(random_bytes($length));
        return $token;
    }

    

    /**
     * Returns the currently logging in user or null if the user isn't logged in
     *
     * @return ?User
     */
    function getCurrentUser()
    {
        $token = !empty($_COOKIE[self::COOKIE_NAME_AUTH_TOKEN])?$_COOKIE[self::COOKIE_NAME_AUTH_TOKEN]:false;
        
        if (!empty($token))
        {
            $tokenInfo = $this->getTokenInfo($token);

            if (!empty($tokenInfo))
            {
                $userId = $tokenInfo->userId;
                return ($this->getUsersManagerObject())->getUserById($userId);
            }
        }

        return null;
    }

    /**
     * Checks if a set of credentials is correct, returning the associated user if so, or null if not
     *
     * @param string $email
     * @param string $password
     * @return ?User
     */
    function checkLogin($email, $password)
    {
        if (empty($email)||empty($password))
            return false;

        $user = ($this->getUsersManagerObject())->getUserByEmail($email);

        if (empty($user))
            return null;

        $hash = $user->password;
        
        if (password_verify($password, $hash))
            return $user;

        return null;
    }


    /**
     * Attempts to login the user with the provided credentials
     *
     * @param string $email Email address for the user
     * @param string $password Password for the user
     * @param boolean $clearOldTokens If set to true, existing auth tokens will be removed
     * @return ?User
     */
    function loginUser($email, $password, $clearOldTokens=true)
    {
        if ($user = $this->checkLogin($email,$password))
        {
            if ($clearOldTokens)
                $this->removeUserTokens($user->id);

            do {
                $token = self::createToken();
            } while ($this->getTokenInfo($token)!=false);

            $this->storeToken($user->id, $token);
            setcookie(self::COOKIE_NAME_AUTH_TOKEN, $token, -1, '/');

            return $user;
        }

        return null;
    }

    /**
     * Logs the current user out of their account by clearing the auth token cookie
     * and removing the auth token
     *
     * @return void
     */
    function logoutUser()
    {
        if (!empty($_COOKIE[self::COOKIE_NAME_AUTH_TOKEN]))
        {
            $token = $_COOKIE[self::COOKIE_NAME_AUTH_TOKEN];
            $this->clearToken($token);
        }

        setcookie(self::COOKIE_NAME_AUTH_TOKEN, null, -1, '/');
    }

    /**
     * Removes all auth tokens associated with the given user id
     *
     * @param int $userId
     * @return void
     */
    function removeUserTokens($userId)
    {
        global $wpdb;
        $sql = 'DELETE FROM ' . $wpdb->prefix . EWN_Schema::NONUSER_AUTH_TOKENS . ' WHERE userId=%d';
        $sql = $wpdb->prepare($sql, $userId);

        $wpdb->query($sql);
    }

    /**
     * Removes a specific auth token from the table
     *
     * @param string $token
     * @return void
     */
    function clearToken($token)
    {
        global $wpdb;
        $sql = 'DELETE FROM ' . $wpdb->prefix . EWN_Schema::NONUSER_AUTH_TOKENS . ' WHERE token=%s LIMIT 1';
        $sql = $wpdb->prepare($sql, $token);

        $wpdb->query($sql);
    }

    /**
     * Retrieves the information about the given token
     *
     * @param string $token
     * @return object
     */
    function getTokenInfo($token)
    {
        global $wpdb;
        $sql = 'SELECT * FROM ' . $wpdb->prefix . EWN_Schema::NONUSER_AUTH_TOKENS . ' WHERE token = %s LIMIT 1';
        $sql = $wpdb->prepare($sql, $token);

        $result = $wpdb->get_row($sql);

        return $result;
    }

    /**
     * Stores a token in the database
     *
     * @param int $userId
     * @param string $token
     * @param boolean|string $created The date the token should be recorded as being created, if false the current time will be used
     * @return int The row id containing the inserted token data
     */
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