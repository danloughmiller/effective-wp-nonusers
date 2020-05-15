<?php
namespace EffectiveWPNonUsers;
class EWN_Schema extends \EffectiveWPToolkit\WPSchema
{

    public const NONUSER_TABLE = 'effwp_nonusers';
    public const NONUSER_META_TABLE = 'effwp_nonuser_meta';
    public const NONUSER_ROLES_TABLE='effwp_nonuser_roles';
    public const NONUSER_AUTH_TOKENS='effwp_nonuser_authtokens';

    static $version = 1.0;

    static function createTables()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix;

        parent::createTable($wpdb, self::getUsersSchema($prefix, self::$version, $charset_collate));
        parent::createTable($wpdb, self::getUsersMetaSchema($prefix, self::$version, $charset_collate));
        //parent::createTable($wpdb, self::getUserRolesSchema($prefix, self::$version, $charset_collate));
        parent::createTable($wpdb, self::getUserAuthTokensSchema($prefix, self::$version, $charset_collate));
    }

    static function getUsersSchema($prefix, $version = 1, $charset = 'ENGINE=InnoDB DEFAULT CHARSET=utf8')
    {
        $tableName = $prefix . self::NONUSER_TABLE;

        $sql = "
          CREATE TABLE IF NOT EXISTS $tableName (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `email` varchar(128) NOT NULL,
          `password` varchar(255) NOT NULL,
          `status` int(11) NOT NULL,
          `registered` datetime NOT NULL,
          `confirmed` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `email` (`email`),
          KEY `registered` (`registered`),
          KEY `status` (`status`)
          ) $charset;
          ";

        return $sql;
    }

    static function getUsersMetaSchema($prefix, $version = 1, $charset = 'ENGINE=InnoDB DEFAULT CHARSET=utf8')
    {
        return \EffectiveWPNonUsers\UserMeta::getSchema($prefix);
    }

    static function getUserRolesSchema($prefix, $version = 1, $charset = 'ENGINE=InnoDB DEFAULT CHARSET=utf8')
    {
        $tableName = $prefix . self::NONUSER_ROLES_TABLE;

        $sql = "
          CREATE TABLE IF NOT EXISTS $tableName (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `userId` int(11) NOT NULL,
          `role` varchar(128) NOT NULL,
          `roleData` text NOT NULL,
          PRIMARY KEY (`id`),
          KEY `userId` (`userId`),
          KEY `role` (`role`)
          )  $charset;
          ";

        return $sql;
    }

    static function getUserAuthTokensSchema($prefix, $version = 1, $charset = 'ENGINE=InnoDB DEFAULT CHARSET=utf8')
    {
        $tableName = $prefix . self::NONUSER_AUTH_TOKENS;

        $sql = "
        CREATE TABLE IF NOT EXISTS $tableName (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `userId` int(11) NOT NULL,
        `token` varchar(255) NOT NULL,
        `created` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `userId` (`userId`),
        KEY `created` (`created`)
        )  $charset;
        ";

        return $sql;
    }
}

