<?php
namespace EffectiveWPNonUsers;

/**
 * Represents a single user
 * 
 * When implementing a custom user object it's helpful to override getUsersManagerObject and if needeed getUserMetaObject
 * to return the correct manager object(s).
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
 * @property string $confirmation_code
 * @property string $reset_password_code
 */
class User extends \EffectiveDataModel\WPDataModel
{
    const TOKEN_LENGTH = 50;

    const FIELD_EMAIL = 'email';
    const FIELD_PASSWORD = 'password';
    const FIELD_STATUS = 'status';
    const FIELD_REGISTERED = 'registered';
    const FIELD_CONFIRMED = 'confirmed';
    const FIELD_FIRST_NAME = 'firstName';
    const FIELD_LAST_NAME = 'lastName';
    const FIELD_CONFIRMATION_CODE = 'confirmation_code';
    const FIELD_RESET_PASSWORD_CODE = 'reset_password_code';
    

    function __construct()
    {
        parent::__construct(EWN_Schema::NONUSER_TABLE);
    }

    /**
     * Returns the object to be used for user management
     *
     * @return UsersManager
     */
    function getUsersManagerObject()
    {
        return UsersManager::instance();
    }

    /**
     * Returns the object to be used for metadate retrieval and storage
     *
     * @return UserMeta
     */
    function getUserMetaObject()
    {
        return ($this->getUsersManagerObject())->getUserMetaObject();
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
        $this->setField('confirmed', empty($confirmed)?null:current_time('mysql'), true);
    }
    
    function setPassword($password, $applyHash=false) { 
        if ($applyHash) {
            $this->setField('password', ($this->getUsersManagerObject())->passwordHash($password), true);
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
        return !empty($this->confirmed);
    }

    function getMeta($key, $singleValue=true)
    {
        $meta = UserMeta::instance();
        return $meta->getMeta($this->id, $key, $singleValue);
    }

    function updateMeta($key, $value)
    {
        $meta = UserMeta::instance();
        return $meta->updateMeta($this->id, $key, $value);
    }


    protected function createToken($length=self::TOKEN_LENGTH)
    {
        $token = bin2hex(random_bytes($length));
        return $token;
    }

    /**
     * Removes the password reset code, such as after a successful reset
     *
     * @return void
     */
    function clearPasswordReset()
    {
        $this->setField(self::FIELD_RESET_PASSWORD_CODE, '');
    }

    /**
     * Create a reset password token and stores it in the user record
     *
     * @return string
     */
    function createPasswordResetCode()
    {
        do {
            $code = $this->createToken();
        } while (!empty($this->getUsersManagerObject()->getUserByResetCode($code)));
        
        $this->rowData[self::FIELD_RESET_PASSWORD_CODE] = true;
        $this->setField(self::FIELD_RESET_PASSWORD_CODE, $code);

        return $code;
    }

    /**
     * Create an account activation token and stores it in the user record
     *
     * @return string
     */
    function createAccountActivationCode()
    {
        do {
            $code = $this->createToken();
        } while (!empty($this->getUsersManagerObject()->getUserByConfirmationCode($code)));

        $this->rowData[self::FIELD_CONFIRMATION_CODE] = true;
        $this->setField(self::FIELD_CONFIRMATION_CODE, $code);

        return $code;
    }
}
