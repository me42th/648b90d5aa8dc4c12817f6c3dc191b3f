<?php

function crud_load(){
    $data = file_get_contents(DATA_LOCATION);
    $data = json_decode($data);
    return $data;
}

function crud_flush($data){
    $data = array_values($data);
    $data = json_encode($data);
    file_put_contents(DATA_LOCATION,$data);
}

function crud_create($user){
    $data = crud_load();
    $data[] = $user;
    crud_flush($data);
}

function crud_restore($email){
    $data = crud_load();
    foreach($data as $item){
        if($item->email === $email){
            return $item;
        }
    }
    return false;
}

function crud_update($user){
    $data = crud_load();
    foreach($data as &$item){
        if($item->email === $user->email){
            $item = $user;
            break;
        }
    }
    crud_flush($data);
}

function crud_delete($user){
    $data = crud_load();
    foreach($data as $key => $item){
        if($item->email === $user->email){
            unset($data[$key]);
            break;
        }
    }
    crud_flush($data);
}