<?php
namespace EffectiveWPNonUsers;

class User extends \EffectiveDataModel\WPDataModel
{

    function __construct()
    {
        parent::__construct(EWN_Schema::EWN_Schema);
    }
    
    function getEmail() { return $this->getField('email');}
    function getPassword() { return false; }
    function getStatus() { return $this->getField('status');}
    function getRegistered() { return $this->getField('registered');}
    function getConfirmed() { return $this->getField('confirmed');}


}