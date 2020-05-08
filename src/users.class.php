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
            return $user->fromArray($result);
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
        $existing = self::getUserByEmail($email);
        if (!empty($existing)) {
            throw new UserEmailExistsException();
            return false;
        }

        //Supply missing data
        if (empty($data['status'])) $data['status'] = self::USER_STATUS_NEW;
        if (empty($data['registered'])) $data['registered'] = current_time('mysql');

        $user = new User();
        $user->fromArray($data);

        return $user->save();
    }


    /* User Meta */
    static function getUserMeta($userId, $field=false, $singleValue=false)
    {

    }

    static function updateUserMeta($userId, $field, $value)
    {

    }

    static function addUserMeta($userId, $field, $value)
    {

    }

    static function deleteUserMeta($userId, $field, $value=false)
    {

    }
    /* End User Meta */


}


class UserRequiredFieldMissing extends Exception
{
    public function __construct($fields='')
    {
        $msg = (is_array($fields)?implode(',',$fields) . ' missing from user creation': $fields);
        parent::__construct($msg);
    }
}

class UserEmailExistsException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}