<?php
namespace EffectiveWPNonUsers;

require_once('schema.php');
require_once('user.class.php');
require_once('users.class.php');

if (defined('EFFECTIVE_DEBUG'))
    require_once(realpath(__DIR__ . '/../tests/tests.php'));