<?php

function do_register(){
    if($_POST['person']??false){
        register_post();
    } else {
        register_get();
    }
}

function register_get(){
    render_view('register');
}

function register_post(){
    $validation_errors = validate_register($_POST['person']);
    if(count($validation_errors) == 0){
        unset($_POST['person']['password-confirm']);
        $_POST['person']['mail_validation'] = false;
        $_POST['person']['password'] = md5($_POST['person']['password']);
        crud_create($_POST['person']);
        $email = $_POST['person']['email'];
        $url = APP_URL."?page=mail-validation&token=";
        $url .= ssl_crypt($email);
        send_mail($email,"Ative a conta",$url);
        header("Location: /?page=login&from=register");
    } else {
        $messages = [
            'errors' => $validation_errors
        ];
        render_view('register', $messages);
    }
}

function do_login(){
    $messages = [];
    switch(@$_GET['from']){
        case 'register':
            $messages['success'] = "Você ainda precisa confirmar o email!";
        break;
        case 'login':
            do_login_authentication();
            $messages['errors'] = [
                "email" => "Email ou senha invalidos",
                "password" => "Email ou senha invalidos"
            ];
        break;
        case 'validation-success':
            $messages['success'] = "Sua conta esta ativa!";
        break;
        case 'change-success':
            $messages['success'] = "Senha alterada com sucesso!";
        break;
        case 'change-email-fail':
            $messages['errors']['email'] = 'O link está corrompido, solicite outro!';
        break;
        case 'change-token-fail':
            $messages['errors']['password'] = 'Token expirado!';
        break;
        case 'validation-problem':
            $messages['errors'] = ['email' => "Link inválido ou expirado!"];
        break;
    }
    render_view('login',$messages);
}

function do_login_authentication(){
    $auth_flag = authentication(...$_POST['person']);
    if($auth_flag){
        header("Location: /");
        exit;
    }
}

function do_forget_password(){
    $email = $_POST['person']['email']??false;
    $messages = [];
    if($email){
        $user = crud_restore($email);
        if($user){
            do_forget_password_success($user);
            $messages['success'] = "Email de redefinição enviado para $email";
        } else {
            $messages['errors'] = ['forget' => "Email invalido!"];
        }
    }
    render_view('forget_password',$messages);
}

function do_forget_password_success($user){
    $email = $user->email;
    $time = (new DateTime)->format('Y-m-d');
    $token = base64_encode("$email:$time");
    $url = APP_URL."?page=change-password&token=";
    $url .= ssl_crypt($token);
    send_mail($email,"Redefinicao de senha",$url);
}

function do_change_password(){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        do_change_password_post();
    } else {
        do_change_password_get();
    }
}

function do_change_password_post(){
    $token = $_POST['token'];
    $token_data = ssl_decrypt($token);
    $validation_errors = validate_change_password($_POST['person']);
    if($validation_errors){
        $messages = [
            'fields' => (object)$_POST ,
            'errors' => $validation_errors
        ];
        render_view('change_password',$messages);
    }
    if($token_data){
        $token_data = base64_decode($token_data);
        $token_data = explode(":",$token_data);
        $user = crud_restore($token_data[0]);
        if($user){
            $user->password = md5($_POST['person']['password']);
            crud_update($user);
            header("Location: /?page=login&from=change-success");
        }
    }
}

function do_change_password_get(){
    $token = $_GET['token'];
    $token_data = ssl_decrypt($token);
    if($token_data){
        $token_data = base64_decode($token_data);
        $token_data = explode(":",$token_data);
        $today = new DateTime;
        $tomorrow = $today->modify("+1 day");
        if(new DateTime($token_data[1]) > $tomorrow){
            header("Location: /?page=login&from=change-token-fail");
        }
        $user = crud_restore($token_data[0]);
        if($user && !$messages){
            $messages['fields'] = (object)$_GET;
            render_view('change_password',$messages);
        } else {
            header("Location: /?page=login&from=change-email-fail");
        }
    } else {
        header("Location: /?page=login&from=change-token-fail");
    }
}

function do_validation(){
    $email = ssl_decrypt($_GET['token']);
    if($email){
        do_validation_success($email);
    } else {
        do_validation_problem();
    }

}

function do_logout(){
    auth_logout();
    header("Location: /");
}

function do_delete_account(){
    $user = auth_user();
    crud_delete($user);
    do_logout();
}

function do_home(){
    $messages = [];
    $messages['fields'] = auth_user();
    render_view('home',$messages);
}

function do_validation_success($email){
    $user = crud_restore($email);
    if($user){
        $user->mail_validation = true;
        crud_update($user);
        header("Location: /?page=login&from=validation-success");
    } else {
        do_validation_problem();
    }
}

function do_validation_problem(){
    header("Location: /?page=login&from=validation-problem");
}

function do_not_found(){
    http_response_code(404);
    render_view('not_found');
}