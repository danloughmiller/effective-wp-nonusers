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

    function getMetaKey($user_id)
    {
        return static::POST_ROLES_USER_KEY . $user_id;
    }

    function getRolesForUserOnPost($userId, $postId)
    {
        global $wpdb;

        $sql = 'SELECT meta_value FROM ' . $this->getTable() . ' WHERE meta_key=%s AND post_id=%d';
        $sql = $wpdb->prepare($sql, $this->getMetaKey($user_id), $postId);

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
        $this->addUserPostRole($userId, $postId, $role);
    }
}