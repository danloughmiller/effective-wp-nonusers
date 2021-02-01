<?php
namespace EffectiveWPNonUsers;

class PostRoles extends \EffectiveWPToolkit\Singleton
{
    const POST_ROLES_USER_KEY = 'post_roles_user_';

    function USERS()
    {
        return Users::instance();
    }

    protected function instantiatePostRoleObject()
    {
        return new PostRole();
    }

    function getTable()
    {
        global $wpdb;
        return $wpdb->prefix . 'postmeta';
    }

    function getMetaKey($user_id='')
    {
        return static::POST_ROLES_USER_KEY . $user_id;
    }

    function getPostRole($role_id)
    {
        return static::getRole($role_id);
    }

    function getRole($role_id)
    {
        global $wpdb;
        $sql = 'SELECT meta_id as ID, SUBSTRING(meta_key, ' . (strlen(self::POST_ROLES_USER_KEY)+1) . ') as user_id,  post_id, meta_value as `role` FROM ' . $this->getTable() . ' where meta_id=%d AND meta_key LIKE \'' . $this->getMetaKey() . '%\' LIMIT 1';
        $sql = $wpdb->prepare($sql, $role_id);

        return $wpdb->get_row($sql);
    }

    public function getPostsWithUserHavingRole($user_id, $roles=array())
    {
        if (!is_array($roles))
            $roles = array($roles);

        global $wpdb;

        $sql = 'SELECT meta_id as ID, SUBSTRING(meta_key, ' . (strlen(self::POST_ROLES_USER_KEY)+1) . ') as user_id, post_id, meta_value as `role` FROM ' . $this->getTable() . ' WHERE meta_key=%s';
        if (empty($roles))
        {
            $res = $wpdb->get_results(
                $wpdb->prepare($sql, $this->getMetaKey($user_id))
            );
        } else {
            $sql .= ' AND meta_value IN (%s)';
            $res = $wpdb->get_results(
                $wpdb->prepare($sql, $this->getMetaKey($user_id), "'" .implode("', '", $roles) . "'")
            );
        }

        return $res;
    }

    function getRolesForUserOnPost($userId, $postId)
    {
        global $wpdb;

        $sql = 'SELECT meta_value FROM ' . $this->getTable() . ' WHERE meta_key=%s AND post_id=%d';
        $sql = $wpdb->prepare($sql, $this->getMetaKey($userId), $postId);

        $result = $wpdb->get_col($sql);
        return $result;
    }

    function userHasPostRoleOnPost($userId, $postId, $role)
    {
        global $wpdb;

        $sql = 'SELECT post_id FROM ' . $this->getTable() . ' WHERE post_id=%d AND meta_key=%s AND meta_value=%s';
        $sql = $wpdb->prepare($sql, $postId, $this->getMetaKey($userId), $role);

        $result = $wpdb->get_row($sql);

        if (!empty($result))
            return true;

        return false;
    }

    function getPostUsers($postId, $roles = array())
    {
        global $wpdb;

        $sql = '
            SELECT
                meta_id as role_id, 
                SUBSTR(meta_key, ' . (strlen($this->getMetaKey())+1) . ') as user_id, 
                meta_value as role
            FROM 
                ' . $this->getTable() . ' 
            WHERE 
                post_id=' . intval($postId) . ' AND 
                meta_key LIKE \'' . $this->getMetaKey() . '%\'
            ';
        
        if (!empty($roles)) {
            if (is_string($roles))
                $roles = array($roles);

            $role_string = "'" . implode("', '", $roles) . "'";
            $sql .= ' AND meta_value IN (' . $role_string . ')';
        }

        $results = $wpdb->get_results($sql);

        return $results;
    }
 
    function addUserPostRole($userId, $postId, $role)
    {
        if (!$this->userHasPostRoleOnPost($userId, $postId, $role))
            add_post_meta($postId, $this->getMetaKey($userId), $role);
    }

    function removeUserPostRole($userId, $postId, $role=false)
    {
        if (!empty($role)) {
            delete_post_meta($postId, $this->getMetaKey($userId), $role);
        } else {
            delete_post_meta($postId, $this->getMetaKey($userId));
        }
    }

    function changeUserPostRole($userId, $postId, $oldRole, $newRole)
    {
        $this->removeUserPostRole($userId, $postId, $oldRole);
        $this->addUserPostRole($userId, $postId, $newRole);
    }
}