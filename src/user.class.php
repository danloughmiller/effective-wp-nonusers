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
    function setConfirmed($confirmed) { $this->setField('confirmed', $confirmed); }

}