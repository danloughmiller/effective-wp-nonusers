<?php
namespace EffectiveWPNonUsers;

class EWN_Schema
{
  static $version = 1.0;

  static function createTables()
  {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $prefix = $wpdb->prefix;

    self::createTable(self::getUsersSchema($prefix, self::$version, $charset_collate));
    self::createTable(self::getUsersMetaSchema($prefix, self::$version, $charset_collate));
    self::createTable(self::getUserRolesSchema($prefix, self::$version, $charset_collate));
  }

  static function createTable($wpdb, $schema_sql)
  {
    $prefix = $wpdb->prefix;

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $schema_sql );

  }

  static function getUsersSchema($prefix, $version=1, $charset='ENGINE=InnoDB DEFAULT CHARSET=utf8')
  {
    $tableName = $prefix.'effwp_nonusers';

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

  static function getUsersMetaSchema($prefix, $version=1, $charset='ENGINE=InnoDB DEFAULT CHARSET=utf8')
  {
    $tableName = $prefix.'effwp_nonuser_meta';

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

  static function getUserRolesSchema($prefix, $version=1, $charset='ENGINE=InnoDB DEFAULT CHARSET=utf8')
  {
    $tableName = $prefix.'effwp_nonuser_roles';

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