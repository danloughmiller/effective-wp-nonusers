<?php
namespace EffectiveWPNonUsers;

/**
 * Adds additional fields to the basic user account for storing names,
 * addresses, and phone information
 * 
 * @since 1.1 First and Last name are now part of the core table and user class
 */
class NamedUser extends User
{
    //public const META_FIRST_NAME = 'profile_first_name'; //@since 1.1
    //public const META_LAST_NAME = 'profile_last_name'; //@since 1.1
    public const META_PHONE = 'profile_phone';
    public const META_POSTAL_CODE = 'profile_postal_code';

    public const META_ADDRESS = 'profile_address';
    public const META_ADDRESS2 = 'profile_address2';
    public const META_CITY = 'profile_city';
    public const META_STATE = 'profile_state';
    public const META_COUNTRY = 'profile_country';


    /**
     * @since 1.1 - Moved to User class
    function getFirstName() { return $this->getMeta(self::META_FIRST_NAME, true); }
    function setFirstName($name) { return $this->updateMeta(self::META_FIRST_NAME, $name); }

    function getLastName() { return $this->getMeta(self::META_LAST_NAME, true); }
    function setLastName($name) { return $this->updateMeta(self::META_LAST_NAME, $name); }
    */

    /**
     * Returns a formatted string containing the first and last name
     */
    function getName() { 
        return $this->getFirstName() . (!empty($this->getLastName())?' '.$this->getLastName():'');
    } 

    function getPhone() { return $this->getMeta(self::META_PHONE, true); }
    function setPhone($name) { return $this->updateMeta(self::META_PHONE, $name); }

    function getAddress() { return $this->getMeta(self::META_ADDRESS, true); }
    function setAddress($name) { return $this->updateMeta(self::META_ADDRESS, $name); }

    function getAddress2() { return $this->getMeta(self::META_ADDRESS2, true); }
    function setAddress2($name) { return $this->updateMeta(self::META_ADDRESS2, $name); }

    function getCity() { return $this->getMeta(self::META_CITY, true); }
    function setCity($name) { return $this->updateMeta(self::META_CITY, $name); }

    function getState() { return $this->getMeta(self::META_STATE, true); }
    function setState($name) { return $this->updateMeta(self::META_STATE, $name); }

    function getCountry() { return $this->getMeta(self::META_COUNTRY, true); }
    function setCountry($name) { return $this->updateMeta(self::META_COUNTRY, $name); }

    function getPostalCode() { return $this->getMeta(self::META_POSTAL_CODE, true); }
    function setPostalCode($postalCode) { return $this->updateMeta(self::META_POSTAL_CODE, $postalCode); }

}

