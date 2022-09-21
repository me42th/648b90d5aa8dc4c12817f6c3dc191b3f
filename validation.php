<?php

function validate_register($data){
    $errors = [];
    if(strlen($data['password']) < 10){
        $errors['password'] = 'A senha deve ter 10 caracteres ou mais';
    }
    if($data['password'] !== $data['password-confirm']){
        $errors['password-confirm'] = 'A senha e a confirmação estão diferentes';
    }
    if(crud_restore($data['email'])){
        $errors['email'] = 'Email já cadastrado no sistema, informe outro';
    }
    return $errors;
}


function validate_change_password($data){
    $errors = [];
    if(strlen($data['password']) < 10){
        $errors['password'] = 'A senha deve ter 10 caracteres ou mais';
    }
    if($data['password'] !== $data['password-confirm']){
        $errors['password-confirm'] = 'A senha e a confirmação estão diferentes';
    }
    return $errors;
}
