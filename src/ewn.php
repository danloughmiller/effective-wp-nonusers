<?php
namespace EffectiveWPNonUsers;

require_once('schema.php');
require_once('user.class.php');
require_once('users.class.php');
require_once('login.class.php');
require_once('usermeta.class.php');
require_once('roles.class.php');
require_once('postroles.class.php');

if (defined('EFFECTIVE_DEBUG'))
    require_once(realpath(__DIR__ . '/../tests/tests.php'));