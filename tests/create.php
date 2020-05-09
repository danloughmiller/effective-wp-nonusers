<?php
namespace EffectiveWPNonUsers;

if (!empty($_GET['effective_test']) && $_GET['effective_test']=='ewn_create_user') {
    try {
        $userId = Users::createUser(array(
            'email'=>'dan@effectwebagency.com',
            'password'=>'test1234'
        ));
    } catch (UserEmailExistsException $ex) {

    }

    echo 'Create Result: ';
    var_dump($userId);


    var_dump(Users::checkLogin('dan@effectwebagency.com', 'test12345'));
    var_dump(Users::checkLogin('dan@effectwebagency.com', 'test1234'));
    exit;
}