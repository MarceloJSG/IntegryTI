<?php

header('Content-Type: application/json; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer instalado manualmente
require __DIR__ . '/vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Método no permitido."
    ]);
    exit;
}

// Correo que recibirá los mensajes
$destinatario = "Soporte@integryti.cl";

$nombre   = trim($_POST["nombre"] ?? "");
$email    = trim($_POST["email"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$empresa  = trim($_POST["empresa"] ?? "");
$servicio = trim($_POST["servicio"] ?? "");
$mensaje  = trim($_POST["mensaje"] ?? "");

// Validación básica
if ($nombre === "" || $email === "" || $telefono === "" || $servicio === "" || $mensaje === "") {
    echo json_encode([
        "success" => false,
        "message" => "Faltan campos obligatorios."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "El correo ingresado no es válido."
    ]);
    exit;
}

$mail = new PHPMailer(true);

try {
    // Configuración SMTP Gmail
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    $mail->Username   = 'contacto@integryti.cl';
    $mail->Password   = 'xgem fzbe ppow toga';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Charset
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('contacto@integryti.cl', 'Formulario IntegryTI');

    $mail->addAddress($destinatario);


    $mail->addReplyTo($email, $nombre);

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Nuevo contacto desde formulario IntegryTI';

    $mail->Body = "
        <h2>Nuevo mensaje desde el sitio web de IntegryTI</h2>
        <p><strong>Nombre:</strong> {$nombre}</p>
        <p><strong>Correo:</strong> {$email}</p>
        <p><strong>Teléfono:</strong> {$telefono}</p>
        <p><strong>Empresa:</strong> {$empresa}</p>
        <p><strong>Servicio requerido:</strong> {$servicio}</p>
        <p><strong>Mensaje:</strong><br>{$mensaje}</p>
    ";

    $mail->AltBody = "
Nuevo mensaje desde el sitio web de IntegryTI

Nombre: $nombre
Correo: $email
Teléfono: $telefono
Empresa: $empresa
Servicio requerido: $servicio

Mensaje:
$mensaje
";

    $mail->send();

    echo json_encode([
        "success" => true,
        "message" => "Formulario enviado correctamente."
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "No se pudo enviar el correo.",
        "error" => $mail->ErrorInfo
    ]);
}

?>