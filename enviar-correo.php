<?php

header('Content-Type: application/json; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

// Cargar configuración privada
$config = require dirname(__DIR__) . '/config-mail.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Método no permitido."
    ]);
    exit;
}

$nombre   = trim($_POST["nombre"] ?? "");
$email    = trim($_POST["email"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$empresa  = trim($_POST["empresa"] ?? "");
$servicio = trim($_POST["servicio"] ?? "");
$mensaje  = trim($_POST["mensaje"] ?? "");
$website  = trim($_POST["website"] ?? "");

// Honeypot anti-spam
if ($website !== "") {
    echo json_encode([
        "success" => false,
        "message" => "Solicitud rechazada."
    ]);
    exit;
}

// Validación básica
if ($nombre === "" || $email === "" || $telefono === "" || $servicio === "" || $mensaje === "") {
    echo json_encode([
        "success" => false,
        "message" => "Faltan campos obligatorios."
    ]);
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "El correo ingresado no es válido."
    ]);
    exit;
}

// Limitar largo de campos
if (
    mb_strlen($nombre) > 100 ||
    mb_strlen($email) > 150 ||
    mb_strlen($telefono) > 30 ||
    mb_strlen($empresa) > 100 ||
    mb_strlen($servicio) > 100 ||
    mb_strlen($mensaje) > 2000
) {
    echo json_encode([
        "success" => false,
        "message" => "Uno o más campos superan el largo permitido."
    ]);
    exit;
}

// Validar servicio permitido
$serviciosPermitidos = [
    "Soporte técnico",
    "Redes y cableado",
    "Mantenimiento",
    "Desarrollo de Software",
    "Asesoría tecnológica"
];

if (!in_array($servicio, $serviciosPermitidos, true)) {
    echo json_encode([
        "success" => false,
        "message" => "Servicio no válido."
    ]);
    exit;
}

// Escapar datos para el correo HTML
$nombreSafe   = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
$emailSafe    = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$telefonoSafe = htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8');
$empresaSafe  = htmlspecialchars($empresa, ENT_QUOTES, 'UTF-8');
$servicioSafe = htmlspecialchars($servicio, ENT_QUOTES, 'UTF-8');
$mensajeSafe  = nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));

$mail = new PHPMailer(true);

try {
    // Configuración SMTP Gmail
    $mail->isSMTP();
    $mail->Host       = $config['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['SMTP_USER'];
    $mail->Password   = $config['SMTP_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $config['SMTP_PORT'];

    // Charset
    $mail->CharSet = 'UTF-8';

    // Remitente y destinatario
    $mail->setFrom($config['SMTP_FROM'], $config['SMTP_FROM_NAME']);
    $mail->addAddress($config['MAIL_TO']);
    $mail->addReplyTo($email, $nombre);

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Nuevo contacto desde formulario IntegryTI';

    $mail->Body = "
        <h2>Nuevo mensaje desde el sitio web de IntegryTI</h2>
        <p><strong>Nombre:</strong> {$nombreSafe}</p>
        <p><strong>Correo:</strong> {$emailSafe}</p>
        <p><strong>Teléfono:</strong> {$telefonoSafe}</p>
        <p><strong>Empresa:</strong> {$empresaSafe}</p>
        <p><strong>Servicio requerido:</strong> {$servicioSafe}</p>
        <p><strong>Mensaje:</strong><br>{$mensajeSafe}</p>
    ";

    $mail->AltBody = "
Nuevo mensaje desde el sitio web de IntegryTI

Nombre: {$nombre}
Correo: {$email}
Teléfono: {$telefono}
Empresa: {$empresa}
Servicio requerido: {$servicio}

Mensaje:
{$mensaje}
";

    $mail->send();

    echo json_encode([
        "success" => true,
        "message" => "Formulario enviado correctamente."
    ]);

} catch (Exception $e) {
    error_log("Error formulario IntegryTI: " . $mail->ErrorInfo);

    echo json_encode([
        "success" => false,
        "message" => "No se pudo enviar el correo. Intenta nuevamente más tarde."
    ]);
}

?>