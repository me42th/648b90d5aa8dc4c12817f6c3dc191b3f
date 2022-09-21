<?php

function send_mail($recipient,$subject,$content){
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->SMTPSecure = '**tls**';
    $mail->IsSMTP(true);
    $mail->Host = "smtp.gmail.com"; // Endereço do servidor SMTP
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->Username = EMAIL_ADDRESS; // Usuário do servidor SMTP
    $mail->Password = EMAIL_PASSWORD; // Senha do servidor SMTP
    $mail->From = EMAIL_ADDRESS; // Seu e-mail
    $mail->FromName = "ScubaPHP"; // Seu nome
    $mail->AddAddress($recipient);
    $mail->Subject  = $subject; // Assunto da mensagem
    $mail->Body = $content;
    $mail->AltBody = $content;
    $enviado = $mail->Send();
}
