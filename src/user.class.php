<?php
namespace EffectiveWPNonUsers;

class User extends \EffectiveDataModel\WPDataModel
{

    function __construct()
    {
        parent::__construct(EWN_Schema::NONUSER_TABLE);
    }
    
    function getEmail() { return $this->getField('email');}
    function getPassword() { return $this->getField('password'); }
    function getStatus() { return $this->getField('status');}
    function getRegistered() { return $this->getField('registered');}
    function getConfirmed() { return $this->getField('confirmed');}

    function setEmail($email) { $this->setField('email', $email); }
    function setPassword($password, $applyHash=false) { 
        if ($applyHash) {
            $this->setField('password', Users::passwordHash($password));
        } else {
            $this->setField('password', $password); }
        }
    function setStatus($status) { $this->setField('status', $status); }
    function setRegistered($registered) { $this->setField('registered', $registered); }
    function setConfirmed($confirmed) { $this->setField('confirmed', $confirmed===false?'0000-00-00 00:00:00':$confirmed); }

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