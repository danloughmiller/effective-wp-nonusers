<?php
namespace EffectiveWPNonUsers;

if (!empty($_GET['effective_test']) && $_GET['effective_test']=='ewn_create_user') {
    $userId = Users::createUser(array(
        'email'=>'dan@effectwebagency.com',
        'password'=>'test1234'
    ));

    echo 'Create Result: ';
    var_dump($userId);
    exit;
}