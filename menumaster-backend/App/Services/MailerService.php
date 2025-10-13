<?php
namespace App\Services;

use Exception;

class MailerService
{
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        $this->fromEmail = getenv('MM_MAIL_FROM') ?: 'no-reply@menumaster.local';
        $this->fromName  = getenv('MM_MAIL_NAME') ?: 'MenuMaster';
    }

    /**
     * Envía comprobante por correo con adjuntos (XML y opcional PDF). Usa PHPMailer si está disponible.
     */
    public function enviarComprobante(string $to, ?string $xmlPath, ?string $pdfPath = null): bool
    {
        if (!$to || !$xmlPath || !file_exists($xmlPath)) {
            throw new Exception('Correo destino y XML válido son requeridos para enviar comprobante');
        }

        // Intentar PHPMailer si existe en vendor
        $phpMailerAvailable = file_exists(BASE_PATH . '/vendor/autoload.php');
        if ($phpMailerAvailable) {
            require_once BASE_PATH . '/vendor/autoload.php';
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);


                $mail->isSMTP();
                $mail->Host       = getenv('MM_SMTP_HOST') ?: 'localhost';
                $mail->Port       = (int)(getenv('MM_SMTP_PORT') ?: 25);
                $mail->SMTPAuth   = (bool)(getenv('MM_SMTP_AUTH') ?: false);
                if ($mail->SMTPAuth) {
                    $mail->Username = getenv('MM_SMTP_USER') ?: '';
                    $mail->Password = getenv('MM_SMTP_PASS') ?: '';
                }
                $secure = getenv('MM_SMTP_SECURE') ?: '';
                if (in_array($secure, ['tls','ssl'], true)) {
                    $mail->SMTPSecure = $secure;
                }

                $mail->setFrom($this->fromEmail, $this->fromName);
                $mail->addAddress($to);
                $mail->Subject = 'Comprobante de Factura Electrónica';
                $mail->Body    = 'Adjuntamos su factura electrónica en formato XML (UBL 2.1).';
                $mail->AltBody = 'Adjuntamos su factura electrónica (XML).';
                $mail->addAttachment($xmlPath, basename($xmlPath), 'base64', 'application/xml');
                if ($pdfPath && file_exists($pdfPath)) {
                    $mail->addAttachment($pdfPath, basename($pdfPath), 'base64', 'application/pdf');
                }
                $mail->send();
                return true;
            } catch (Exception $e) {
                // Fallback abajo
            }
        }

        // Fallback simple usando mail(); adjuntos no soportados directamente sin MIME manual
        $subject = 'Comprobante de Factura Electrónica';
        $headers = 'From: ' . $this->fromName . ' <' . $this->fromEmail . ">\r\n";
        $headers .= 'Content-Type: text/plain; charset=UTF-8';
        $message = 'Su factura electrónica ha sido emitida. Por favor contacte al establecimiento para obtener una copia si no la recibe adjunta.';

        // Nota: El envío de adjuntos vía mail() requiere construcción MIME; omitimos por simplicidad si no hay PHPMailer
        return mail($to, $subject, $message, $headers);
    }
}