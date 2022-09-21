<?php

function render_view($template,$messages = []){
    $content = load_content($template,$messages);
    echo $content;
    exit;
}

function load_content($template,$messages){
    $validation_errors = $messages['errors']??[];
    $fields = $messages['fields']??'';
    $success_msg = $messages['success']??'';
    $content = file_get_contents(VIEW_FOLDER."$template.view");
    $content = put_error_data($content,$validation_errors);
    $content = put_success_msg($content,$success_msg);
    $content = put_old_values($content);
    $content = put_field_values($content,$fields);
    return $content;
}

function put_field_values($content,$fields){
    $field_places = get_field_places($content);
    $field_values = prepare_field_values($fields,$field_places);
    $content = data_binding($content,$field_values);
    return $content;
}

function prepare_field_values($fields,$field_places){
    $field_values = [];
    foreach ($field_places as $place){
        $field = str_replace('{{field_','',$place);
        $field = str_replace('}}','',$field);
        $field = str_replace('_','-',$field);
        $field_values[$place] = $fields->{$field}??'';
    }
    return $field_values;
}

function put_success_msg($content,$success_msg){
    $success_msg = success_msg_maker($success_msg);
    $content = data_binding($content, ['{{success}}'=>$success_msg]);
    return $content;
}

function put_old_values($content){
    $value_places = get_value_places($content);
    $values = prepare_old_values($value_places);
    $content = data_binding($content,$values);
    return $content;
}


function prepare_old_values($value_places){
    $values = [];
    foreach ($value_places as $place){
        $field = str_replace('{{value_','',$place);
        $field = str_replace('}}','',$field);
        $field = str_replace('_','-',$field);
        $values[$place] = $_POST['person'][$field]??'';
    }
    return $values;
}

function put_error_data($content,$validation_errors){
    $error_places = get_error_places($content);
    $errors_msg = create_errors_msg($validation_errors,$error_places);
    $content = data_binding($content, $errors_msg);
    return $content;
}

function data_binding($content, $values){
    foreach($values as $place => $value){
        $content = str_replace($place,$value,$content);
    }
    return $content;
}

function create_errors_msg($validation_errors, $error_places){
    $errors_msg = [];
    foreach ($error_places as $place){
        $field = str_replace('{{error_','',$place);
        $field = str_replace('}}','',$field);
        $field = str_replace('_','-',$field);
        $errors_msg[$place] = isset($validation_errors[$field])?error_msg_maker($validation_errors[$field]):'';
    }
    return $errors_msg;
}

function error_msg_maker($msg){
    $error = '<span class="mensagem-erro">'.$msg.'</span>';
    return $error;
}

function success_msg_maker($msg){
    $sucess = '<div class="mensagem-sucesso">
        <p>'.$msg.'</p>
    </div>';
    return $sucess;
}

function get_sucess_places($content){
    return get_place_of('sucess', $content);
}

function get_error_places($content){
    return get_place_of('error', $content);
}

function get_field_places($content){
    return get_place_of('field', $content);
}

function get_value_places($content){
    return get_place_of('value', $content);
}

function get_place_of($field, $content){
    $pattern= "/{{".$field."\w+}}/";
    preg_match_all($pattern, $content, $match);
    return $match[0]??[];
}