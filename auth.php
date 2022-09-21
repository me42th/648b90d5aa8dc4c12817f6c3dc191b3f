<?php

function authentication($email, $password){
    $user = crud_restore($email);
    $auth_flag = false;
    if($user){
        $auth_flag = $user->mail_validation;
        $auth_flag = $auth_flag && $user->password === md5($password);
    }
    if($auth_flag){
        $_SESSION['user'] = json_encode($user);
    }
    return $auth_flag;
}

function auth_user(){
    return json_decode($_SESSION['user']);
}

function auth_logout(){
    unset($_SESSION['user']);
}