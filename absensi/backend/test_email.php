<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'karepwae6@gmail.com';
    $mail->Password   = 'APP_PASSWORD_GMAIL';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('akshakarya0@gmail.com', 'Absensi System');
    $mail->addAddress('karepwae6@gmail.com');

    $mail->Subject = 'TES EMAIL';
    $mail->Body    = 'Jika email ini masuk, SMTP kamu BENAR.';

    $mail->send();
    echo "EMAIL TERKIRIM";
} catch (Exception $e) {
    echo "GAGAL: {$mail->ErrorInfo}";
}
