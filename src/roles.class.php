<?php
namespace EffectiveWPNonUsers;

class Roles extends \EffectiveWPToolkit\Singleton
{
    function USERS()
    {
        return Users::instance();
    }

    protected function instantiateRoleObject()
    {
        return new Role();
    }

    function getUserRoles($userId)
    {
        global $wpdb;

        $sql = 'SELECT * FROM ' . $wpdb->prefix . EWN_Schema::NONUSER_ROLES_TABLE . ' WHERE userId=%s';
        $sql = $wpdb->prepare($sql, $userId);

        $result = $wpdb->get_results($sql);

        if (!empty($result)) {
            $roles = array();
            foreach ($result as $r) {
                $role = static::instantiateRoleObject();
                $role->fromArray($r);
                $roles[] = $role;
            }
            
            return $roles;
        }

        return array();
    }

    function userHasRole($userId, $role)
    {
        global $wpdb;

        $sql = 'SELECT * FROM ' . $wpdb->prefix . EWN_Schema::NONUSER_ROLES_TABLE . ' WHERE userId=%d AND role=%s';
        $sql = $wpdb->prepare($sql, $userId, $role);

        $result = $wpdb->get_row($sql);

        if (!empty($result))
            return true;

        return false;
    }

    function addUserRole($userId, $role, $roleData=array())
    {
        if (!$this->userHasRole($userId, $role)) {
            $r = static::instantiateRoleObject();

            $r->setUserId($userId);
            $r->setRole($role);
            $r->setRoleData($roleData);
            $r->save();

            return $r;
        }

        return false;
    }

    function removeUserRole($userId, $role)
    {
        global $wpdb;

        $sql = 'DELETE FROM ' . $wpdb->prefix . EWN_Schema::NONUSER_ROLES_TABLE . ' WHERE userId=%s AND role=%s';
        $sql = $wpdb->prepare($sql, $userId, $role);

        $result = $wpdb->query($sql);
        return $result;
    }

}


class Role extends \EffectiveDataModel\WPDataModel
{

    function __construct()
    {
        parent::__construct(EWN_Schema::NONUSER_ROLES_TABLE);
    }

    function getUserId() { return $this->getField('userId');}
    function getRole() { return $this->getField('role');}
    function getRoleData() { return json_decode($this->getField('roleData'));}

    function setUserId($userId) { $this->setField('userId', $userId); }
    function setRole($role) { $this->setField('role', $role); }
    function setRoleData($data) { $this->setField('roleData', json_encode($data));}

}