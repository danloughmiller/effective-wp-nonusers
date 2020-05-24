<?php
namespace EffectiveWPNonUsers;

class UserMeta extends \EffectiveWPToolkit\WPMeta
{
    const EWN_META_TABLE='effwp_nonuser_meta';

    function getTable($applyPrefix=false)
    {
        global $wpdb;
        return ($applyPrefix?$wpdb->prefix:'') . self::EWN_META_TABLE;
    }

}