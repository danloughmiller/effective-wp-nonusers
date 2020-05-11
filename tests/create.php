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

    //var_dump(Users::checkLogin('dan@effectwebagency.com', 'test12345'));
    //var_dump(Users::checkLogin('dan@effectwebagency.com', 'test1234'));

    UserMeta::updateMeta(1, 'first_name', 'Dan');
    UserMeta::updateMeta(1, 'last_name', 'L');

    //UserMeta::addMeta(1, 'permissions', 'this');
    //UserMeta::addMeta(1, 'permissions', 'that');
    //UserMeta::addMeta(1, 'permissions', 'the other thing');

    //UserMeta::updateMeta(1, 'anarray', array('this', 'that'));

    var_dump(UserMeta::getMeta(1, 'first_name', true));
    var_dump(UserMeta::getMeta(1, 'last_name'));
    var_dump(UserMeta::getMeta(1, 'permissions'));
    var_dump(UserMeta::getMeta(1, 'anarray', false));
    
    exit;
}