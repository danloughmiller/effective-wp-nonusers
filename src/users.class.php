<?php
namespace EffectiveWPNonUsers;

class Users
{
    const USER_STATUS_NEW='new';
    const USER_STATUS_ACTIVE='active';
    const USER_STATUS_DELETED='deleted';
    const USER_STATUS_DISABLED='disabled';

    static function getUser($id)
    {
        global $wpdb;

        $sql = 'SELECT * FROM ' . $wpdb->prefix . EWN_Schema::NONUSER_TABLE . ' WHERE id=%d';
        $sql = $wpdb->prepare($sql, $email);

        $result = $wpdb->get_row($sql);

        if (!empty($result)) {
            $user = new User();
            return $user->fromArray($result);
        }

        return false;
    }

    static function getUserByEmail($email)
    {
        global $wpdb;

        $sql = 'SELECT * FROM ' . $wpdb->prefix . EWN_Schema::NONUSER_TABLE . ' WHERE email=%s';
        $sql = $wpdb->prepare($sql, $email);

        $result = $wpdb->get_row($sql);

        if (!empty($result)) {
            $user = new User();
            $user->fromArray($result);
            return $user->save();
        }

        return false;
    }

    static function createUser($data = array())
    {
        //Validate required fields
        $required = array('email', 'password');
        foreach ($required as $field) {
            if (empty($data[$field]))
            {
                throw new UserRequiredFieldMissing($field);
                return;
            }
        }

        //Verify user is unique
        $existing = self::getUserByEmail($data['email']);
        if (!empty($existing)) {
            throw new UserEmailExistsException();
            return false;
        }

        //Supply missing data
        if (empty($data['status'])) $data['status'] = self::USER_STATUS_NEW;
        if (empty($data['registered'])) $data['registered'] = current_time('mysql');

        //Hash the password
        $data['password'] = self::passwordHash($data['password']);

        $user = new User();
        $user->fromArray($data);
        $user->save();
        return $user->getId();
    }

    /* Roles */


    /* End Roles */

    /* Security */
    static function passwordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    static function checkLogin($email, $password)
    {
        $user = self::getUserByEmail($email);

        if (empty($user))
            return false;

        $hash = $user->getPassword();
        
        if (password_verify($password, $hash))
            return $user;

        return false;
    }

    static function confirmAccount($userId, $confirmed=false)
    {
        if ($confirmed===false)
            $confirmed = current_time('mysql');

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . EWN_Schema::NONUSER_TABLE,
            array('confirmed'=>$confirmed),
            array('id'=>$userId)
        );
    }
    /* End Security */

}


class UserRequiredFieldMissing extends \Exception
{
    public function __construct($fields='', $code=0)
    {
        $msg = (is_array($fields)?implode(',',$fields) . ' missing from user creation': $fields);
        parent::__construct($msg, $code);
    }
}

class UserEmailExistsException extends \Exception
{
    public function __construct($message='email already exists', $code=0)
    {
        parent::__construct($message, $code);
    }
}

class UserNoFoundException extends \Exception
{
    public function __construct($message='user could not be found', $code=0)
    {
        parent::__construct($message, $code);
    }
}