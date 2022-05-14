<?php
namespace EffectiveWPNonUsers;

/**
 * Represents a single user
 * 
 * @since 1.1 - First and last name now columns in main user table instead of meta
 * 
 * @property string $email
 * @property string $password
 * @property string $status
 * @property string $registered
 * @property string $confirmed
 * @property string $firstName
 * @property string $lastName
 */
class User extends \EffectiveDataModel\WPDataModel
{
    const FIELD_EMAIL = 'email';
    const FIELD_PASSWORD = 'password';
    const FIELD_STATUS = 'status';
    const FIELD_REGISTERED = 'registered';
    const FIELD_CONFIRMED = 'confirmed';
    const FIELD_FIRST_NAME = 'firstName';
    const FIELD_LAST_NAME = 'lastName';

    function __construct()
    {
        parent::__construct(EWN_Schema::NONUSER_TABLE);
    }

    /**
     * Returns an array of fields which should be mapped to meta fields instead of table fields
     *
     * @return array
     */
    protected function getMetaFields() { return array(); }

    function __get($field)
    {
        if (in_array($field, $this->getMetaFields()))
            return $this->getMeta($field);

        return parent::__get($field);
    }

    function __set($field, $value)
    {
        if (in_array($field, $this->getMetaFields()))
            return $this->updateMeta($field, $value);

        return parent::__set($field, $value);
    }

    function __isset($name)
    {
        if (in_array($name, $this->getMetaFields()))
            return true;

        return parent::__isset($name);
    }


    function setConfirmed($confirmed) { 
        $this->setField('confirmed', $confirmed===false?'0000-00-00 00:00:00':current_time('mysql'), true);
    }
    
    function setPassword($password, $applyHash=false) { 
        if ($applyHash) {
            $this->setField('password', Users::passwordHash($password), true);
        } else {
            $this->setField('password', $password, true);
        }
    }
    function setPasswordHash($passwordHash)
    {
        $this->setPassword($passwordHash, false);
    }
    
    function isConfirmed()
    {
        return $this->confirmed != '0000-00-00 00:00:00';
    }

    protected function USERS()
    {
        return Users::getInstance();
    }

    protected function META()
    {
        $users = $this->USERS();
        return $users->META();
    }

    function ROLES()
    {
        $users = $this->USERS();
        return $users->ROLES();
    }

    function getMeta($key, $singleValue=true)
    {
        $meta = $this->META();
        return $meta->getMeta($this->id, $key, $singleValue);
    }

    function updateMeta($key, $value)
    {
        $meta = $this->META();
        return $meta->updateMeta($this->id, $key, $value);
    }

    function getRoles()
    {
        $roles = $this->ROLES();
        return $roles->getUserRoles($this->id);
    }

    function getRoleNames()
    {
        $role = $this->getRoles();
        $s = array();
        foreach ($role as $r) {
            $s[] = $r->getRole();
        }

        return $s;
    }

    function hasRole($role)
    {
        $roles = $this->ROLES();
        return $roles->userHasRole($this->id, $role);
    }

    function addRole($role, $roleData=array())
    {
        $roles = $this->ROLES();
        return $roles->addUserRole($this->id, $role, $roleData);
    }

    function removeRole($role)
    {
        $roles = $this->ROLES();
        return $roles->removeUserRole($this->id, $role);
    }

}
