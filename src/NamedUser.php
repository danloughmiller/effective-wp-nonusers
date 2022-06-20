<?php
namespace EffectiveWPNonUsers;

/**
 * Adds additional fields to the basic user account for storing names,
 * addresses, and phone information
 * 
 * @since 1.1.0 First and Last name are now part of the core table and user class
 * @since 2.0.0 Meta fields now use 
 * 
 * @property string $profile_phone
 * @property string $profile_postal_code
 * @property string $profile_address
 * @property string $profile_address2
 * @property string $profile_city
 * @property string $profile_state
 * @property string $profile_country
 * 
 */
class NamedUser extends User
{
    
    public const META_PHONE = 'profile_phone';
    public const META_POSTAL_CODE = 'profile_postal_code';
    public const META_ADDRESS = 'profile_address';
    public const META_ADDRESS2 = 'profile_address2';
    public const META_CITY = 'profile_city';
    public const META_STATE = 'profile_state';
    public const META_COUNTRY = 'profile_country';
   
    /**
     * Returns a formatted string containing the first and last name
     */
    function getName() { 
        $name = $this->firstName;
        
        if (!empty($name) && !empty($this->lastName))
            $name .= ' ';

        $name .= $this->lastName;

        return $name;
    }

    protected function getMetaFields()
    {
        return parent::getMetaFields() + array(
            self::META_PHONE,
            self::META_POSTAL_CODE,
            self::META_ADDRESS,
            self::META_ADDRESS2,
            self::META_CITY,
            self::META_STATE,
            self::META_COUNTRY
        );
    }
}

