<?php
namespace EffectiveWPNonUsers;
class EWN_Schema extends \EffectiveDataModel\WPSchema
{

    public const NONUSER_TABLE = 'effwp_nonusers';
    public const NONUSER_META_TABLE = 'effwp_nonuser_meta';
    public const NONUSER_ROLES_TABLE='effwp_nonuser_roles';

    static $version = 1.0;

    static function createTables()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix;

        parent::createTable($wpdb, self::getUsersSchema($prefix, self::$version, $charset_collate));
        parent::createTable($wpdb, self::getUsersMetaSchema($prefix, self::$version, $charset_collate));
        parent::createTable($wpdb, self::getUserRolesSchema($prefix, self::$version, $charset_collate));
    }

    static function getUsersSchema($prefix, $version = 1, $charset = 'ENGINE=InnoDB DEFAULT CHARSET=utf8')
    {
        $tableName = $prefix . self::NONUSER_TABLE;

        $sql = "
          CREATE TABLE IF NOT EXISTS $tableName (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `email` varchar(128) NOT NULL,
          `password` varchar(128) NOT NULL,
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
        $tableName = $prefix . self::NONUSER_META_TABLE;

        $sql = "
          CREATE TABLE IF NOT EXISTS $tableName (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `userId` int(11) NOT NULL,
          `metaKey` varchar(128) NOT NULL,
          `metaValue` text NOT NULL,
          PRIMARY KEY (`id`),
          KEY `userId` (`userId`),
          KEY `metaKey` (`metaKey`)
          )  $charset;
          ";

        return $sql;
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
}

