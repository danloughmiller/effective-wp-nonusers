<?php
namespace EffectiveWPNonUsers;

class UserMeta extends \EffectiveWPToolkit\WPMeta
{
    const EWN_META_TABLE='effwp_nonuser_meta';

    static function getTable()
    {
        global $wpdb;
        return $wpdb->prefix . self::EWN_META_TABLE;
    }

}