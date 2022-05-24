<?php
namespace EffectiveWPNonUsers;

use EffectiveWPToolkit\Singleton;

class UsersManager extends Singleton
{
    const USER_STATUS_NEW='new';
    const USER_STATUS_ACTIVE='active';
    const USER_STATUS_DELETED='deleted';
    const USER_STATUS_DISABLED='disabled';

    /**
     * Retrieves the user meta manager object
     *
     * @return UserMeta
     */
    function getUserMetaObject()
    {
        return UserMeta::instance();
    }

    /**
     * Creates an empty instance of the User object
     *
     * @return User
     */
    protected function instantiateUserObject()
    {
        return new User();
    }

    /**
     * Returns the prefixed table name used to store user information
     *
     * @return string
     */
    function getPrefixedTable()
    {
        global $wpdb;
        return $wpdb->prefix . EWN_Schema::NONUSER_TABLE;
    }

    /**
     * Returns the number of users
     *
     * @param boolean $confirmedOnly If true only confirmed users will be counted
     * @return integer
     */
    function getUsersCount($confirmedOnly=true)
    {
        global $wpdb;
        
        $sql = 'SELECT COUNT(*) from ' . $this->getPrefixedTable() . ($confirmedOnly?' WHERE confirmed>\'0000-00-00 00:00:00\'':'');
        return $wpdb->get_var($sql);
    }

    /**
     * Converts an array of user ids into user objects
     *
     * @param int[] $user_ids
     * @return User[]
     */
    function userIdsToUsers($user_ids)
    {
        $users = array();
        foreach($user_ids as $r) {
            $user = $this->getUserById($r);

            if (!empty($user))
                $users[] = $user;
        }
        return $users;
    }

    /**
     * Gets a list of unfiltered user ids by limit/offset
     *
     * @param integer $limit
     * @param integer $offset
     * @return int[]
     */
    function getUserIds($limit=-1, $offset=0)
    {
        global $wpdb;
        $sql = 'SELECT ID from ' . $this->getPrefixedTable() . ' ORDER BY email' . ($limit>0?' LIMIT ' . $offset . ','.$limit:' ');
        $res = $wpdb->get_col($sql);

        return $res;
    }

    /**
     * Gets a list of User objects by limit, offset
     *
     * @param integer $limit
     * @param integer $offset
     * @return User[]
     */
    function getUsers($limit=-1, $offset=0)
    {
        $user_ids = $this->getUserIds($limit, $offset);
        return $this->userIdsToUsers($user_ids);
    }

    /**
     * Retrieves a specific user
     *
     * @param int $id
     * @return User
     */
    function getUserById($id)
    {
        global $wpdb;

        $sql = 'SELECT * FROM ' . $this->getPrefixedTable() . ' WHERE id=%d';
        $sql = $wpdb->prepare($sql, $id);

        $result = $wpdb->get_row($sql);
        
        if (!empty($result)) {
            $user = $this->instantiateUserObject();
            return $user->fromArray($result);
        }

        return false;
    }

    /**
     * Returns an array of user ids where field provides matches the value provided
     *
     * @param string $field
     * @param string $value
     * @return integer[]
     */
    function getUserIdsByField($field, $value)
    {
        global $wpdb;

        $sql = 'SELECT id FROM ' . $this->getPrefixedTable() . ' WHERE ' . $field . '= %s';
        $sql = $wpdb->prepare($sql, $value);

        $result = $wpdb->get_col($sql);

        return $result;
    }

    /**
     * Returns an array of users where field provides matches the value provided
     *
     * @param string $field
     * @param string $value
     * @return User[]
     */
    public function getUsersByField($field, $value)
    {
        $user_ids = $this->getUserIdsByField($field, $value);
        return $this->userIdsToUsers($user_ids);
    }

    /**
     * Returns a single User where the field matches the value
     *
     * @param string $field
     * @param string $value
     * @return ?User
     */
    function getUserByField($field, $value)
    {
        $user_ids = $this->getUserIdsByField($field, $value);

        if (!empty($user_ids[0]))
            return $this->getUserById($user_ids[0]);

        return null;
    }

    /**
     * Returns a user given their email
     *
     * @param string $email
     * @return User
     */
    function getUserByEmail($email)
    {
        return $this->getUserByField(User::FIELD_EMAIL, $email);
    }


    /**
     * Returns a password hash
     *
     * @param string $password
     * @return string
     */
    function passwordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT, array('cost'=>12));
    }


/*
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
            ' . $this->getPrefixedTable() . ' as users
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

        $results = $wpdb->get_col($sql);

        if (empty($results))
            return $results;

        if ($ids_only)
            return $results;

        $users = array();
        foreach ($results as $result) {
            $users[] = $this->getUserById($result);
        }

        return $users;
    }
    */

    /**
     * Retrieves users with the specified meta value
     * 
     * @param string $metaKey The meta key the value is expected to appear in
     * @param string $metaValue The meta value the user needs to have for the specified key
     * @param bool $ids_only If true will return an array of user ids, otherwise will return an array of user class instances
     */
    /*
    function getUsersWithMetaValue($metaKey, $metaValue, $ids_only=false) {
        global $wpdb;

        $sql = '
        SELECT 
            users.ID
        FROM 
            ' . $this->getPrefixedTable() . ' as users
        INNER JOIN 
            ' .  (UserMeta::instance())->field . ' as meta 
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
            $users[] = $this->getUserById($result);
        }

        return $users;
    }
*/


}