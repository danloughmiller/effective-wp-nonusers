<?php
namespace EffectiveWPNonUsers;

use EffectiveWPToolkit\Singleton;

class Users extends Singleton
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

    function getUsers($limit=-1, $offset=0, $ids_only=false)
    {
        global $wpdb;
        $sql = 'SELECT ID from ' . $this->getTable() . ' ORDER BY email' . ($limit>0?' LIMIT ' . $offset . ','.$limit:' ');
        $res = $wpdb->get_col($sql);

        if ($ids_only)
            return $res;

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

    function createUser($data = array(), $ignoreValidation=false)
    {
        //Validate required fields
        if (!$ignoreValidation) {
            $required = $this->getRequiredFields();
            foreach ($required as $field) {
                if (empty($data[$field]))
                {
                    throw new UserRequiredFieldMissing($field);
                    return;
                }
            }
        }

        //Verify user is unique
        if (!empty($data['email'])) {
            $existing = $this->getUserByEmail($data['email']);
            if (!empty($existing)) {
                throw new UserEmailExistsException();
                return false;
            }
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

    /**
     * Retrieves a list of all user ids
     */
    function getAllUserIds()
    {
        global $wpdb;

        $sql = '
        SELECT 
            ID
        FROM 
            ' . $this->getTable() . ' as users
        ORDER BY
            ' . User::FIELD_LAST_NAME . ', ' . User::FIELD_FIRST_NAME . '
        ';

        return $wpdb->get_col($sql);
    }

    /**
     * Retrieves a list of user ids of users without a confirmation date
     */
    function getPendingUserIds()
    {
        global $wpdb;

        $sql = '
        SELECT 
            ID
        FROM 
            ' . $this->getTable() . ' as users
        WHERE
            confirmed=\'0000-00-00 00:00:00\'
        ORDER BY
            ' . User::FIELD_LAST_NAME . ', ' . User::FIELD_FIRST_NAME . '
        ';

        return $wpdb->get_col($sql);
    }


    function searchUsers($term, $ids_only=false, $unconfirmed_only=false) {
        global $wpdb;

        $sql = '
        SELECT 
            users.ID,
            users.email as email,
            firstName as first_name,
            lastName as lastName,
            MATCH(email,firstName,lastName) AGAINST(\'%s\') AS score
        FROM 
            ' . $this->getTable() . ' as users
        WHERE
            1=1
            ' . ($unconfirmed_only?'AND confirmed=\'0000-00-00 00:00:00\'':'') . '
        HAVING
            score>0
        ORDER BY
            score DESC,
            lastName,
            firstName,
            email
        LIMIT
            100';

        $sql = $wpdb->prepare($sql, $term);

        /*
        $terms = explode(' ', $term);
        $regex = implode('|', $terms);

        $sql = '
        SELECT 
            users.ID,
            users.email as email,
            meta.metaValue as first_name, 
            meta2.metaValue as last_name,
            (   (users.email REGEXP %s) +
                (meta.metaValue REGEXP %s) +
                (meta2.metaValue REGEXP %s)
            ) as score
        FROM 
            ' . $this->getTable() . ' as users
        LEFT JOIN 
            ' . $wpdb->prefix . UserMeta::EWN_META_TABLE . ' as meta 
                ON meta.objectId=users.id AND 
                meta.metaKey=\'profile_first_name\' 
        LEFT JOIN 
            ' . $wpdb->prefix . UserMeta::EWN_META_TABLE . ' as meta2 
                ON meta2.objectId=users.id AND 
                meta2.metaKey=\'profile_last_name\' 
        HAVING
            score>0
        ORDER BY
            score DESC,
            last_name,
            first_name,
            email
        LIMIT
            100';
       

        $sql = $wpdb->prepare($sql, $regex, $regex, $regex);
        */
        //echo $sql;
        $results = $wpdb->get_col($sql);

        if (empty($results))
            return $results;

        if ($ids_only)
            return $results;

        $users = array();
        foreach ($results as $result) {
            $users[] = $this->getUser($result);
        }

        return $users;
    }

    /**
     * Retrieves users with the specified meta valu
     * 
     * @param string $metaKey The meta key the value is expected to appear in
     * @param string $metaValue The meta value the user needs to have for the specified key
     * @param bool $ids_only If true will return an array of user ids, otherwise will return an array of user class instances
     */
    function getUsersWithMetaValue($metaKey, $metaValue, $ids_only=false) {
        global $wpdb;

        $sql = '
        SELECT 
            users.ID
        FROM 
            ' . $this->getTable() . ' as users
        INNER JOIN 
            ' .  $this->META()->getTable(true) . ' as meta 
                ON meta.objectId=users.id AND 
                meta.metaKey=%s AND
                meta.metaValue=%s';

        $sql = $wpdb->prepare($sql, $metaKey, $metaValue);
        
        $results = $wpdb->get_col($sql);

        if (empty($results))
            return $results;

        if ($ids_only)
            return $results;

        $users = array();
        foreach ($results as $result) {
            $users[] = $this->getUser($result);
        }

        return $users;
    }

    /* Security */
    function passwordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    function checkLogin($email, $password)
    {
        if (empty($email)||empty($password))
            return false;

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

    function unconfirmAccount($userId)
    {
        global $wpdb;
        
        $wpdb->update(
            $this->getTable(),
            array('confirmed'=>'0000-00-00 00:00:00'),
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