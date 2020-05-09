<?php
namespace EffectiveWPNonUsers;

class Login
{

    const TOKEN_LENGTH = 100;
    //const COOKIE_NAME_AUTH_KEY = 
    
    static function checkLogin($email, $password)
    {
        return Users::checkLogin($email, $password);
    }

    static function createAuthToken($length=self::TOKEN_LENGTH)
    {
        $token = bin2hex(random_bytes(64));
    }


    /* Cookie Management */
    static function loginUser($email, $password)
    {

    }


    static function logoutUser()
    {

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

}