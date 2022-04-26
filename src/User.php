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

    function __set($name, $value)
    {
        switch ($name)
        {
            case self::FIELD_CONFIRMED:
                $this->setConfirmed($value);
                break;
            case self::FIELD_PASSWORD:
                $this->setPassword($value);
                break;
            default:
                parent::__set($name, $value);
        }
    }
    
    function getEmail() { return $this->getField(self::FIELD_EMAIL);}
    function setEmail($email) { $this->setField(self::FIELD_EMAIL, $email); }

    function getPassword() { return $this->getField(self::FIELD_PASSWORD); }
    
    
    function getStatus() { return $this->getField('status');}
    function setStatus($status) { $this->setField('status', $status); }
    
    function getRegistered() { return $this->getField('registered');}
    function setRegistered($registered) { $this->setField('registered', $registered); }
    
    function getConfirmed() { return $this->getField('confirmed');}
    function setConfirmed($confirmed) { 
        $this->setField('confirmed', $confirmed===false?'0000-00-00 00:00:00':current_time('mysql'));
    }

    function getFirstName() { return $this->getField('firstName'); }
    function setFirstName($name) { return $this->setField('firstName', $name); }

    function getLastName() { return $this->getField('lastName'); }
    function setLastName($name) { return $this->setField('lastName', $name); }

    
    function setPassword($password, $applyHash=false) { 
        if ($applyHash) {
            $this->setField('password', Users::passwordHash($password));
        } else {
            $this->setField('password', $password);
        }
    }
    function setPasswordHash($passwordHash)
    {
        $this->setPassword($passwordHash, false);
    }
    
    
    
    function isConfirmed()
    {
        return $this->getConfirmed() != '0000-00-00 00:00:00';
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
        return $meta->getMeta($this->getId(), $key, $singleValue);
    }

    function updateMeta($key, $value)
    {
        $meta = $this->META();
        return $meta->updateMeta($this->getId(), $key, $value);
    }

    function getRoles()
    {
        $roles = $this->ROLES();
        return $roles->getUserRoles($this->getId());
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
        return $roles->userHasRole($this->getId(), $role);
    }

    function addRole($role, $roleData=array())
    {
        $roles = $this->ROLES();
        return $roles->addUserRole($this->getId(), $role, $roleData);
    }

    function removeRole($role)
    {
        $roles = $this->ROLES();
        return $roles->removeUserRole($this->getId(), $role);
    }

}
