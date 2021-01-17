<?php
namespace EffectiveWPNonUsers;

class Users extends \EffectiveWPToolkit\Singleton
{
    const USER_STATUS_NEW='new';
    const USER_STATUS_ACTIVE='active';
    const USER_STATUS_DELETED='deleted';
    const USER_STATUS_DISABLED='disabled';

    function META()
    {
        return UserMeta::getInstance();
    }

    function ROLES()
    {
        return Roles::instance();
    }

    protected function instantiateUserObject()
    {
        return new User();
    }

    function getTable()
    {
        global $wpdb;
        return $wpdb->prefix . EWN_Schema::NONUSER_TABLE;
    }

    function getUsersCount($confirmedOnly=true)
    {
        global $wpdb;
        
        $sql = 'SELECT COUNT(*) from ' . $this->getTable() . ' WHERE confirmed>\'0000-00-00 00:00:00\'';
        return $wpdb->get_var($sql);
    }

    function getUsers($limit=50, $offset=0)
    {
        global $wpdb;
        $sql = 'SELECT ID from ' . $this->getTable() . ' ORDER BY email LIMIT ' . $offset . ','.$limit;
        $res = $wpdb->get_col($sql);

        $users = array();
        foreach($res as $r) {
            $users[] = $this->getUser($r);
        }
        return $users;
    }

    function getUser($id)
    {
        global $wpdb;

        $sql = 'SELECT * FROM ' . $this->getTable() . ' WHERE id=%d';
        $sql = $wpdb->prepare($sql, $id);

        $result = $wpdb->get_row($sql);

        if (!empty($result)) {
            $user = $this->instantiateUserObject();
            return $user->fromArray($result);
        }

        return false;
    }

    function getUserById($id)
    {
        return $this->getUser($id);
    }

    function getUserByEmail($email)
    {
        global $wpdb;

        $sql = 'SELECT id FROM ' . $this->getTable() . ' WHERE email=%s';
        $sql = $wpdb->prepare($sql, $email);

        $result = $wpdb->get_row($sql);

        if (!empty($result)) {
            return $this->getUserById($result->id);
        }

        return false;
    }

    function getRequiredFields()
    {
        return array('email', 'password');
    }

    function createUser($data = array())
    {
        //Validate required fields
        $required = $this->getRequiredFields();
        foreach ($required as $field) {
            if (empty($data[$field]))
            {
                throw new UserRequiredFieldMissing($field);
                return;
            }
        }

        //Verify user is unique
        $existing = $this->getUserByEmail($data['email']);
        if (!empty($existing)) {
            throw new UserEmailExistsException();
            return false;
        }

        //Supply missing data
        if (empty($data['status'])) $data['status'] = self::USER_STATUS_NEW;
        if (empty($data['registered'])) $data['registered'] = current_time('mysql');

        //Hash the password
        $data['password'] = $this->passwordHash($data['password']);

        $user = new User();
        $user->fromArray($data);
        $user->save();
        return $user->getId();
    }

    /* Security */
    function passwordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    function checkLogin($email, $password)
    {
        $user = $this->getUserByEmail($email);

        if (empty($user))
            return false;

        $hash = $user->getPassword();
        
        if (password_verify($password, $hash))
            return $user;

        return false;
    }

    function confirmAccount($userId, $confirmed=false)
    {
        if ($confirmed===false)
            $confirmed = current_time('mysql');

        global $wpdb;
        $wpdb->update(
            $this->getTable(),
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